<?php

namespace Paheko\Plugin\Caisse\Entities;

use Paheko\Accounting\Years;
use Paheko\Entities\Accounting\Transaction;
use Paheko\Email\Emails;
use Paheko\DB;
use Paheko\Entity;
use Paheko\Template;
use Paheko\UserException;
use Paheko\Utils;
use Paheko\ValidationException;
use Paheko\Users\Users;
use Paheko\Users\DynamicFields;
use Paheko\Services\Services_User;

use const Paheko\PLUGIN_ROOT;

use Paheko\Plugin\Caisse\POS;
use Paheko\Plugin\Caisse\Tabs;

use KD2\Mail_Message;
use KD2\DB\EntityManager;

class Session extends Entity
{
	const TABLE = POS::TABLES_PREFIX . 'sessions';

	protected ?int $id;
	protected ?int $id_location = null;
	protected \DateTime $opened;
	protected ?\DateTime $closed;
	protected string $open_user;
	protected ?string $close_user;
	protected ?int $result = null;
	protected ?int $nb_tabs = null;

	protected array $_balances = [];

	public function balance(int $id_method)
	{
		$this->_balances[$id_method] ??= EntityManager::findOne(SessionBalance::class, 'SELECT * FROM @TABLE WHERE id_session = ? AND id_method = ?;', $this->id(), $id_method);

		if (!isset($this->_balances[$id_method])) {
			$balance = new SessionBalance;
			$balance->set('id_session', $this->id());
			$balance->set('id_method', $id_method);
			$this->_balances[$id_method] = $balance;
		}

		return $this->_balances[$id_method];
	}

	public function hasOpenNotes(): bool
	{
		return DB::getInstance()->test(POS::tbl('tabs'), 'session = ? AND closed IS NULL', $this->id);
	}

	public function getFirstOpenTab(): ?Tab
	{
		return EntityManager::findOne(Tab::class, 'SELECT * FROM @TABLE WHERE closed IS NULL ORDER BY opened DESC LIMIT 1;');
	}

	public function listTabIdsWithFeesButNoUser(): array
	{
		return DB::getInstance()->getAssoc(POS::sql('SELECT DISTINCT t.id, t.id
			FROM @PREFIX_tabs t
			INNER JOIN @PREFIX_tabs_items ti ON ti.tab = t.id
			WHERE t.session = ? AND t.user_id IS NULL AND ti.id_fee IS NOT NULL;'), $this->id());
	}

	public function close(string $user_name, array $user_balances, array $payments)
	{
		$db = DB::getInstance();

		if ($this->hasOpenNotes()) {
			throw new UserException('Il y a des notes qui ne sont pas clôturées.');
		}

		$missing = $this->listTabIdsWithFeesButNoUser();

		if (count($missing)) {
			throw new UserException(sprintf("Les notes suivantes comportent une inscription à une activité mais ne sont pas liées à un membre : %s\nMerci de créer une fiche membre et associer la note au membre.", implode(', ', $missing)));
		}

		$payments = array_map('intval', $payments);
		$payments = implode(',', $payments);

		$check_payments = $db->firstColumn(sprintf(POS::sql('SELECT COUNT(*) FROM @PREFIX_tabs_payments tp
			INNER JOIN @PREFIX_tabs t ON t.id = tp.tab AND t.session = ?
			INNER JOIN @PREFIX_methods m ON m.id = tp.method
			WHERE tp.id NOT IN (%s) AND m.type = %d LIMIT 1;'), $payments, Method::TYPE_TRACKED), $this->id);

		if ($check_payments) {
			throw new UserException('Certains paiements n\'ont pas été cochés comme vérifiés');
		}

		$db->begin();

		foreach ($this->listClosingBalances() as $balance) {
			$value = $user_balances[$balance->id]['amount'] ?? '';

			if (trim($value) === '') {
				throw new UserException(sprintf('Le solde "%s" ne peut être laissé vide.', $balance->name));
			}

			$value = Utils::moneyToInteger($value);

			if ($value !== $balance->expected_total
				&& empty($user_balances[$balance->id]['confirm'])) {
				var_dump($user_balances); exit;
				throw new UserException(sprintf('Le solde constaté à la clôture ne correspond pas pour "%s", si le recompte est juste, cocher la case pour confirmer l\'erreur.', $balance->name));
			}

			$b = $this->balance($balance->id);
			$b->set('close_amount', $value);
			$b->set('error_amount', $value - $balance->expected_total);
			$b->save();
		}

		$db->preparedQuery(POS::sql('UPDATE @PREFIX_sessions SET
			closed = datetime(\'now\', \'localtime\'),
			close_user = ?,
			result = ?,
			nb_tabs = ?
			WHERE id = ?'),
			$user_name,
			$this->getItemsTotal(),
			$this->getTabsCount(),
			$this->id()
		);

		// Update stock
		$db->preparedQuery(POS::sql('INSERT INTO @PREFIX_products_stock_history (product, change, date, item, event)
			SELECT p.id, -SUM(ti.qty), ti.added, ti.id, NULL
				FROM @PREFIX_tabs_items ti
				INNER JOIN @PREFIX_products p ON p.id = ti.product
				INNER JOIN @PREFIX_tabs t ON t.id = ti.tab
				INNER JOIN @PREFIX_sessions s ON s.id = t.session
				WHERE s.closed IS NOT NULL AND ti.product IS NOT NULL AND s.id = ? AND p.stock IS NOT NULL
				GROUP BY ti.id, ti.product;'), $this->id);

		// Update weight
		$db->preparedQuery(POS::sql('INSERT INTO @PREFIX_categories_weight_history (category, change, date, item, type)
			SELECT p.category, -SUM(ti.qty * ti.weight), ti.added, ti.id, NULL
				FROM @PREFIX_tabs_items ti
				INNER JOIN @PREFIX_products p ON p.id = ti.product
				INNER JOIN @PREFIX_tabs t ON t.id = ti.tab
				INNER JOIN @PREFIX_sessions s ON s.id = t.session
				WHERE s.closed IS NOT NULL AND ti.product IS NOT NULL AND s.id = ? AND ti.weight IS NOT NULL
				GROUP BY ti.id, ti.product;'), $this->id);

		$select = sprintf('FROM @PREFIX_tabs_items ti
			INNER JOIN @PREFIX_tabs t ON t.id = ti.tab
			WHERE ti.product = @PREFIX_products.id
			AND t.session = %d', $this->id);
		$db->preparedQuery(POS::sql(sprintf('UPDATE @PREFIX_products SET stock = -(SELECT SUM(ti.qty) %s) + stock WHERE stock IS NOT NULL AND id IN (SELECT DISTINCT ti.product %1$s);', $select)));

		// Create subscriptions
		$sql = POS::sql('SELECT ti.tab, ti.id, t.user_id, ti.id_fee, ti.total, ti.qty
			FROM @PREFIX_tabs_items ti
			INNER JOIN @PREFIX_tabs t ON t.id = ti.tab
			WHERE ti.id_fee IS NOT NULL AND session = ?;');

		foreach ($db->iterate($sql, $this->id) as $row) {
			try {
				$su = Services_User::createFromFee($row->id_fee, $row->user_id, $row->total, true, $row->qty);

				// Ignore duplicates
				if ($su->isDuplicate()) {
					continue;
				}

				$su->save();
				$db->update(TabItem::TABLE, ['id_subscription' => $su->id()], 'id = ' . (int)$row->id);
			}
			catch (UserException $e) {
				throw new UserException(sprintf('Note n°%d : %s', $row->tab, $e->getMessage()));
			}
		}

		$db->commit();
	}

	public function sendTo(string $address): void
	{
		$msg = new Mail_Message;
		$msg->setHeader('Subject', sprintf('Clôture de caisse n°%d du %s', $this->id, date('d/m/Y à H:i')));
		$msg->setHeader('To', $address);
		$msg->setHeader('From', Emails::getFromHeader());
		$msg->addPart('text/html', $this->export(true, 1), sprintf('session-%d.html', $this->id));
		$msg->setBody('Voir les détails dans le contenu HTML ci-joint.');
		Emails::sendMessage(Emails::CONTEXT_SYSTEM, $msg);
	}

	public function syncWithYearId(int $id, ?int $id_creator = null): bool
	{
		return POS::syncAccounting($id_creator, Years::get($id), $this->id()) === 1;
	}

	public function getPaymentsTotal()
	{
		return DB::getInstance()->firstColumn(POS::sql('SELECT SUM(tp.amount) FROM @PREFIX_tabs_payments tp
			INNER JOIN @PREFIX_tabs t ON tp.tab = t.id AND t.session = ?'), $this->id);
	}

	public function getItemsTotal()
	{
		return DB::getInstance()->firstColumn(POS::sql('SELECT SUM(ti.total) FROM @PREFIX_tabs_items ti
			INNER JOIN @PREFIX_tabs t ON ti.tab = t.id AND t.session = ?'), $this->id);
	}

	public function getTabsCount()
	{
		return DB::getInstance()->firstColumn(POS::sql('SELECT COUNT(*) FROM @PREFIX_tabs WHERE session = ?'), $this->id);
	}

	public function listPayments()
	{
		return DB::getInstance()->get(POS::sql('SELECT tp.*, t.name AS tab_name,
			m.name AS method_name
			FROM @PREFIX_tabs_payments tp
			INNER JOIN @PREFIX_tabs t ON tp.tab = t.id AND t.session = ?
			INNER JOIN @PREFIX_methods m ON m.id = tp.method
			ORDER BY m.id, tp.date;'), $this->id);
	}

	public function listPaymentTotals()
	{
		return DB::getInstance()->get(POS::sql('SELECT SUM(tp.amount) AS total,
			m.name AS method_name
			FROM @PREFIX_tabs_payments tp
			INNER JOIN @PREFIX_tabs t ON tp.tab = t.id AND t.session = ?
			INNER JOIN @PREFIX_methods m ON m.id = tp.method
			GROUP BY m.id
			ORDER BY method_name;'), $this->id);
	}

	public function listTabsTotals()
	{
		return DB::getInstance()->get(POS::sql('SELECT *,
			(SELECT SUM(total) FROM @PREFIX_tabs_items WHERE tab = t.id) AS total
			FROM @PREFIX_tabs t WHERE session = ? ORDER BY opened;'), $this->id);
	}

	public function listTabsWithItems()
	{
		$db = DB::getInstance();
		$tabs = $db->get(POS::sql('SELECT *, total - paid AS remainder
			FROM (SELECT *,
				(SELECT SUM(total) FROM @PREFIX_tabs_items WHERE tab = t.id) AS total,
				(SELECT SUM(amount) FROM @PREFIX_tabs_payments WHERE tab = t.id) AS paid
				FROM @PREFIX_tabs t WHERE session = ? ORDER BY opened
			);'), $this->id);

		foreach ($tabs as &$tab) {
			$t = Tabs::get($tab->id);
			$tab->items = $t->listItems();
		}

		return $tabs;
	}

	public function listTotalsByCategory()
	{
		return DB::getInstance()->get(POS::sql('SELECT
			SUM(ti.total) AS total,
			SUM(ti.qty) AS count,
			SUM(ti.qty * ti.weight) AS weight,
			ti.category_name,
			c.account
			FROM @PREFIX_tabs_items ti
			LEFT JOIN @PREFIX_products p ON ti.product = p.id
			LEFT JOIN @PREFIX_categories c ON c.id = p.category
			WHERE ti.tab IN (SELECT id FROM @PREFIX_tabs WHERE session = ?)
			GROUP BY category_name;'), $this->id);

	}

	public function listCountsByProduct()
	{
		return DB::getInstance()->get(POS::sql('SELECT
			SUM(ti.total) AS total,
			SUM(ti.qty) AS count,
			SUM(ti.qty * ti.weight) AS weight,
			ti.name, ti.category_name
			FROM @PREFIX_tabs_items ti
			LEFT JOIN @PREFIX_products p ON ti.product = p.id
			WHERE ti.tab IN (SELECT id FROM @PREFIX_tabs WHERE session = ?)
			GROUP BY ti.name ORDER BY ti.category_name, ti.name;'), $this->id);

	}

	public function listClosingBalances(): array
	{
		return DB::getInstance()->get(POS::sql('
			SELECT m.id, m.name, b.open_amount, COALESCE(SUM(p.amount), 0) AS total, COALESCE(SUM(p.amount), 0) + b.open_amount AS expected_total
			FROM @PREFIX_sessions_balances b
			INNER JOIN @PREFIX_methods m ON m.id = b.id_method
			LEFT JOIN @PREFIX_tabs t ON t.session = b.id_session
			LEFT JOIN @PREFIX_tabs_payments p ON p.tab = t.id AND p.method = m.id
			WHERE b.id_session = ?
			GROUP BY m.id;'), $this->id());
	}

	public function listBalances(): array
	{
		return DB::getInstance()->get(POS::sql('
			SELECT m.id, m.name, b.open_amount, b.close_amount, b.error_amount
			FROM @PREFIX_sessions_balances b
			INNER JOIN @PREFIX_methods m ON m.id = b.id_method
			WHERE b.id_session = ?;'), $this->id());
	}

	public function listTrackedPayment()
	{
		return DB::getInstance()->get(POS::sql('
			SELECT p.*, m.name AS method_name FROM @PREFIX_tabs_payments p
			INNER JOIN @PREFIX_tabs t ON t.id = p.tab
			INNER JOIN @PREFIX_methods m ON m.id = p.method
			WHERE t.session = ? AND m.type = ?
			ORDER BY p.date;'), $this->id, Method::TYPE_TRACKED);
	}

	public function export(bool $details = false, int $print = 0)
	{
		$tpl = Template::getInstance();
		$tpl->assign('pos_session', $this);
		$tpl->assign('payments', $this->listPayments());
		$tpl->assign('payments_totals', $this->listPaymentTotals());
		$tpl->assign('tabs', $this->listTabsWithItems());
		$tpl->assign('totals_categories', $this->listTotalsByCategory());
		$tpl->assign('totals_products', $this->listCountsByProduct());
		$tpl->assign('total_payments', $this->getPaymentsTotal());
		$tpl->assign('total_sales', $this->getItemsTotal());
		$tpl->assign('balances', $this->listBalances());

		$tpl->assign('title', sprintf('Session de caisse n°%d du %s', $this->id, Utils::date_fr($this->opened)));

		$tpl->assign('print', (bool) $print);
		$tpl->assign('details', $details);
		$tpl->assign('id_field', DynamicFields::getFirstNameField());

		if ($print === 2) {
			$tpl->PDF(PLUGIN_ROOT . '/templates/session_export.tpl', sprintf('Session de caisse numéro %d du %s', $this->id, Utils::date_fr($this->opened, 'd-m-Y')));
			return null;
		}
		else {
			return $tpl->fetch(PLUGIN_ROOT . '/templates/session_export.tpl');
		}
	}

	public function openTab(?int $user_id = null): Tab
	{
		if (!$this->exists()) {
			throw new \LogicException('Cannot open tab for unsaved session');
		}

		$tab = new Tab;
		$tab->session = $this->id();
		$tab->opened = new \DateTime;
		$tab->user_id = $user_id;

		if ($tab->user_id) {
			$tab->name = Users::getName($tab->user_id);
		}

		$tab->save();
		return $tab;
	}

	public function isSynced(): bool
	{
		return DB::getInstance()->test('acc_transactions', 'reference = ?', 'POS-SESSION-' . $this->id());
	}

	public function getTransaction(): ?Transaction
	{
		return EntityManager::findOne(Transaction::class, 'SELECT * FROM @TABLE WHERE reference = ?;', 'POS-SESSION-' . $this->id());
	}

	public function delete(): bool
	{
		if ($this->isSynced()) {
			throw new UserException('Il n\'est pas possible de supprimer une session de caisse synchronisée avec la comptabilité.');
		}

		return parent::delete();
	}

	public function findOpenTabByUser(int $id): ?Tab
	{
		return EntityManager::findOne(Tab::class, 'SELECT * FROM @TABLE WHERE user_id = ? AND session = ? AND closed IS NULL;', $id, $this->id());
	}
}