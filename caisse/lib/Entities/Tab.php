<?php

namespace Paheko\Plugin\Caisse\Entities;

use Paheko\DB;
use Paheko\UserException;

use Paheko\Plugin\Caisse\POS;
use Paheko\Plugin\Caisse\Products;
use Paheko\Plugin\Caisse\Tabs;
use Paheko\Plugin\Caisse\Sessions;
use Paheko\Entity;
use Paheko\Utils;
use Paheko\ValidationException;

use KD2\DB\EntityManager as EM;

class Tab extends Entity
{
	const TABLE = POS::TABLES_PREFIX . 'tabs';

	protected ?int $id;
	protected int $session;
	protected \DateTime $opened;
	protected ?\DateTime $closed = null;
	protected ?string $name = null;
	protected ?int $user_id = null;

	public int $total;

	const PAYMENT_STATUS_DEBT = 0;
	const PAYMENT_STATUS_PAID = 1;

	protected ?Session $_session = null;

	public function load(array $data): self
	{
		parent::load($data);
		$this->total = $this->total();
		return $this;
	}

	public function session(?Session $session = null): Session
	{
		if (null !== $session) {
			$this->_session = $session;
		}
		elseif (null === $this->_session) {
			$this->_session = Sessions::get($this->session);
		}

		return $this->_session;
	}

	public function total(): int
	{
		$db = DB::getInstance();
		return (int) $db->firstColumn(POS::sql('SELECT SUM(total) FROM @PREFIX_tabs_items WHERE tab = ?;'), $this->id()) ?: 0;
	}

	public function getRemainder(): int
	{
		return (int) DB::getInstance()->firstColumn(POS::sql('SELECT
			COALESCE((SELECT SUM(total) FROM @PREFIX_tabs_items WHERE tab = ?), 0)
			- COALESCE((SELECT SUM(amount) FROM @PREFIX_tabs_payments WHERE tab = ?), 0);'), $this->id, $this->id);
	}

	public function addItemByCode(string $code): void
	{
		$code = trim($code);
		$code = preg_replace('/\s+/', '', $code);

		if (!ctype_digit($code)) {
			throw new UserException('Code barre invalide : ' . $code);
		}

		$id = DB::getInstance()->firstColumn(POS::sql('SELECT id FROM @PREFIX_products WHERE code = ? ORDER BY id LIMIT 1;'), $code);

		if (!$id) {
			throw new UserException('Code barre inconnu : ' . $code);
		}

		$this->addItem($id);
	}

	public function addItem(int $id, ?string $user_weight = null, ?int $price = null, int $type = TabItem::TYPE_PRODUCT)
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$db = DB::getInstance();
		$product = $db->first(POS::sql('SELECT p.*, c.name AS category_name, c.account AS category_account
			FROM @PREFIX_products p
			INNER JOIN @PREFIX_categories c ON c.id = p.category
			WHERE p.id = ?'), $id);

		if (!$product) {
			throw new UserException('This product does not exist: ' . $id);
		}

		$weight = $product->weight;
		$price ??= (int)$product->price;
		$pricing = TabItem::PRICING_QTY;

		if ($weight === Product::WEIGHT_BASED_PRICE) {
			$weight = Utils::weightToInteger($user_weight);
			$pricing = TabItem::PRICING_QTY_WEIGHT;
		}
		elseif ($weight === Product::WEIGHT_REQUIRED) {
			$weight = Utils::weightToInteger($user_weight);
		}

		$item = new TabItem;
		$item->importForm([
			'tab'           => $this->id,
			'product'       => (int)$product->id,
			'qty'           => (int)$product->qty,
			'price'         => $price,
			'weight'        => $weight,
			'name'          => $product->name,
			'category_name' => $product->category_name,
			'description'   => $product->description,
			'account'       => $product->category_account,
			'type'          => $type,
			'pricing'       => $pricing,
			'id_fee'        => $product->id_fee,
		]);
		$item->save();
	}

	public function getItem(int $id): ?TabItem
	{
		return EM::findOne(TabItem::class, 'SELECT * FROM @TABLE WHERE id = ? AND tab = ?;', $id, $this->id());
	}

	public function removeItem(int $id)
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$item = $this->getItem($id);

		if (!$item) {
			return;
		}

		$item->delete();
	}

	public function updateItemQty(int $id, int $qty): void
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$item = $this->getItem($id);
		$item->set('qty', $qty);
		$item->save();
	}

	public function updateItemWeight(int $id, string $weight): void
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$weight = Utils::weightToInteger($weight);

		$item = $this->getItem($id);
		$item->set('weight', $weight);
		$item->save();
	}

	public function updateItemPrice(int $id, string $price): void
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$price = Utils::moneyToInteger($price);

		$item = $this->getItem($id);
		$item->set('price', $price);
		$item->save();
	}

	public function listItems()
	{
		return DB::getInstance()->get(POS::sql('SELECT ti.*,
			GROUP_CONCAT(pm.method, \',\') AS methods
			FROM @PREFIX_tabs_items ti
			LEFT JOIN @PREFIX_products p ON ti.product = p.id
			LEFT JOIN @PREFIX_categories c ON c.id = p.category
			LEFT JOIN @PREFIX_products_methods pm ON pm.product = p.id
			WHERE ti.tab = ?
			GROUP BY ti.id
			ORDER BY ti.id;'), $this->id);
	}

	public function isUserIdMissing(): bool
	{
		if ($this->user_id) {
			return false;
		}

		return DB::getInstance()->test(TabItem::TABLE, 'id_fee IS NOT NULL AND tab = ?', $this->id());
	}

	public function pay(int $method_id, int $amount, ?string $reference, bool $auto_close = true): void
	{
		if ($this->closed) {
			throw new UserException('Il n\'est pas possible de modifier une note clôturée.');
		}

		if ('' === trim($reference)) {
			$reference = NULL;
		}

		$remainder = $this->getRemainder();

		if ($amount >= 0 && $amount > $remainder) {
			throw new UserException('Il n\'est pas possible d\'encaisser un montant supérieur au reste à payer.');
		}
		elseif ($amount < 0 && $amount < $remainder) {
			throw new UserException('Il n\'est pas possible de rembourser un montant supérieur au reste à rembourser.');
		}

		$options = $this->listPaymentOptions();
		$option = $options[$method_id] ?? null;

		if (!$option) {
			throw new UserException('Ce moyen de paiement n\'est pas disponible.');
		}

		if ($option->type === Method::TYPE_DEBT && empty($this->name)) {
			throw new UserException('Il n\'est pas possible d\'enregistrer une ardoise sans nom associé.');
		}

		if (null === $option->max_amount) {
			throw new UserException('Ce moyen de paiement ne peut pas être utilisé pour cette note');
		}
		elseif ($option->max_amount >= 0 && $amount > $option->max_amount) {
			$a = $option->max_amount;
			throw new UserException(sprintf('Ce moyen de paiement ne peut être utilisé pour un montant supérieur à %s€', Utils::money_format($a)));
		}
		elseif ($option->max_amount < 0 && $amount < $option->max_amount) {
			$a = $option->max_amount;
			throw new UserException(sprintf('Ce moyen de paiement ne peut être utilisé pour un montant inférieur à %s€', Utils::money_format($a)));
		}

		if (null !== $reference && $option->type !== Method::TYPE_TRACKED) {
			throw new UserException('Référence indiquée pour un règlement en espèces : vouliez-vous enregistrer un règlement par chèque ?');
		}

		DB::getInstance()->insert(POS::tbl('tabs_payments'), [
			'tab'       => $this->id,
			'method'    => $method_id,
			'amount'    => $amount,
			'reference' => $reference,
			'account'   => $option->account,
			'status'    => $option->type === Method::TYPE_DEBT ? self::PAYMENT_STATUS_DEBT : self::PAYMENT_STATUS_PAID,
		]);

		if ($remainder - $amount === 0 && $auto_close) {
			$this->close();
		}
	}

	public function removePayment(int $id)
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		return DB::getInstance()->delete(POS::tbl('tabs_payments'), 'id = ? AND tab = ?', $id, $this->id);
	}

	public function listPayments()
	{
		return DB::getInstance()->get(POS::sql('SELECT tp.*,
			m.name AS method_name
			FROM @PREFIX_tabs_payments tp
			LEFT JOIN @PREFIX_methods m ON m.id = tp.method
			WHERE tp.tab = ?;'), $this->id);
	}

	public function listPaymentOptions()
	{
		$remainder = $this->getRemainder();

		if ($l = $this->session()->id_location) {
			$where = ' AND m.id_location = ' . (int)$l;
		}
		else {
			$where = '';
		}

		return DB::getInstance()->getGrouped(POS::sql('SELECT id, *,
			CASE
				WHEN max IS NOT NULL AND max > 0 AND paid >= max THEN NULL -- We cannot use this payment method, we paid the max allowed amount with it
				WHEN max IS NOT NULL AND max > 0 AND payable > max THEN max -- We have to pay more than max allowed, then just return max
				WHEN min IS NOT NULL AND payable < min THEN NULL -- We cannot use as the minimum required amount has not been reached
				WHEN :left < 0 THEN MAX(:left, payable)
				ELSE MIN(:left, payable) END AS max_amount
			FROM (SELECT m.*, SUM(pt.amount) AS paid, SUM(i.total) AS payable
				FROM @PREFIX_methods m
				INNER JOIN @PREFIX_products_methods pm ON pm.method = m.id
				INNER JOIN @PREFIX_tabs_items i ON i.product = pm.product AND i.tab = :id
				LEFT JOIN @PREFIX_tabs_payments AS pt ON pt.tab = i.tab AND m.id = pt.method
				WHERE m.enabled = 1 ' . $where . '
				GROUP BY m.id
				ORDER BY position IS NOT NULL DESC, position, name COLLATE NOCASE
			);'), ['id' => $this->id, 'left' => $remainder]);
	}

	public function rename(string $new_name, ?int $user_id) {
		$new_name = trim($new_name);
		$db = DB::getInstance();
		return $db->update(POS::tbl('tabs'), ['name' => $new_name, 'user_id' => $user_id], $db->where('id', $this->id));
	}

	public function renameItem(int $id, string $name)
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$item = $this->getItem($id);
		$item->set('name', trim($name));
		$item->save();
	}

	public function close()
	{
		$remainder = $this->getRemainder();

		if ($remainder != 0) {
			throw new UserException(sprintf("Impossible de clôturer la note: reste %s € à régler.", $remainder / 100));
		}

		return DB::getInstance()->preparedQuery(POS::sql('UPDATE @PREFIX_tabs SET closed = datetime(\'now\',\'localtime\') WHERE id = ?;'), [$this->id]);
	}

	public function reopen() {
		return DB::getInstance()->preparedQuery(POS::sql('UPDATE @PREFIX_tabs SET closed = NULL WHERE id = ?;'), [$this->id]);
	}

	public function delete(): bool {
		$db = DB::getInstance();
		if ($db->count(POS::tbl('tabs_items'), 'tab = ?', $this->id) && $db->test(POS::tbl('tabs'), 'closed IS NULL AND id = ?', $this->id)) {
			throw new UserException('Impossible de supprimer une note qui n\'est pas close');
		}

		return parent::delete();
	}

	public function getUserDebt(): int
	{
		if (empty($this->user_id)) {
			return 0;
		}

		return Tabs::getUnpaidDebtAmount($this->user_id);
	}

	public function addDebt(string $account, int $amount): void
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$db = DB::getInstance();
		$sql = POS::sql('SELECT p.id
			FROM @PREFIX_products p
			INNER JOIN @PREFIX_categories c ON c.id = p.category
			WHERE c.account = ? LIMIT 1;');

		// Get first product matching debt account
		$product_id = $db->firstColumn($sql, $account);

		// Automatically create missing product
		if (!$product_id) {
			$product = Products::createAndSaveForDebtAccount($account);
			$product_id = $product->id();
		}

		$this->addItem($product_id, null, $amount, TabItem::TYPE_PAYOFF);
	}

	public function addUserDebt(): void
	{
		if ($this->closed) {
			throw new UserException('Cette note est close, impossible de modifier la note.');
		}

		$list = Tabs::listDebtsHistory($this->user_id);
		$list->setPageSize(null);
		$due = [];

		// Create list of debts, by third-party account
		foreach ($list->iterate() as $item) {
			$due[$item->account] ??= 0;

			if ($item->type === 'debt') {
				$due[$item->account] += $item->amount;
			}
			else {
				$due[$item->account] -= $item->amount;
			}
		}

		// Remove accounts where they are at zero
		$due = array_filter($due);

		foreach ($due as $account => $amount) {
			$this->addDebt($account, $amount);
		}
	}
}
