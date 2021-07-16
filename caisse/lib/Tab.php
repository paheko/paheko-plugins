<?php

namespace Garradin\Plugin\Caisse;

use Garradin\Config;
use Garradin\DB;
use Garradin\Membres\Cotisations;
use Garradin\UserException;

class Tab
{
	public $id;

	public function __construct(int $id, bool $fetch = true)
	{
		$this->id = $id;

		if ($fetch) {
			$sql = POS::sql('SELECT *,
				COALESCE((SELECT SUM(qty*price) FROM @PREFIX_tabs_items WHERE tab = @PREFIX_tabs.id), 0) AS total
				FROM @PREFIX_tabs WHERE id = ?;');

			$record = DB::getInstance()->first($sql, $id);

			if (!$record) {
				throw new \InvalidArgumentException('Unknown tab ID');
			}

			foreach ($record as $key => $value) {
				$this->$key = $value;
			}
		}
	}

	public function getRemainder(): int
	{
		return (int) DB::getInstance()->firstColumn(POS::sql('SELECT
			COALESCE((SELECT SUM(price * qty) FROM @PREFIX_tabs_items WHERE tab = ?), 0)
			- COALESCE((SELECT SUM(amount) FROM @PREFIX_tabs_payments WHERE tab = ?), 0);'), $this->id, $this->id);
	}

	public function addItem(int $id)
	{
		$db = DB::getInstance();
		$product = $db->first(POS::sql('SELECT p.*, c.name AS category_name, c.account AS category_account
			FROM @PREFIX_products p
			INNER JOIN @PREFIX_categories c ON c.id = p.category
			WHERE p.id = ?'), $id);

		return $db->insert(POS::tbl('tabs_items'), [
			'tab'           => $this->id,
			'product'       => (int)$product->id,
			'qty'           => (int)$product->qty,
			'price'         => (int)$product->price,
			'name'          => $product->name,
			'category_name' => $product->category_name,
			'description'   => $product->description,
			'account'       => $product->category_account,
		]);
	}

	public function removeItem(int $id)
	{
		return DB::getInstance()->delete(POS::tbl('tabs_items'), 'id = ? AND tab = ?', $id, $this->id);
	}

	public function updateItemQty(int $id, int $qty)
	{
		$db = DB::getInstance();
		return $db->update(POS::tbl('tabs_items'),
			['qty' => $qty],
			$db->where('id', $id));
	}

	public function updateItemPrice(int $id, int $price)
	{
		$db = DB::getInstance();
		return $db->update(POS::tbl('tabs_items'),
			['price' => $price],
			$db->where('id', $id));
	}

	public function listItems()
	{
		return DB::getInstance()->get(POS::sql('SELECT ti.*,
			ti.qty * ti.price AS total,
			GROUP_CONCAT(pm.method, \',\') AS methods
			FROM @PREFIX_tabs_items ti
			LEFT JOIN @PREFIX_products p ON ti.product = p.id
			LEFT JOIN @PREFIX_categories c ON c.id = p.category
			LEFT JOIN @PREFIX_products_methods pm ON pm.product = p.id
			WHERE ti.tab = ?
			GROUP BY ti.id
			ORDER BY ti.id;'), $this->id);
	}

	public function pay(int $method_id, int $amount, ?string $reference)
	{
		if ('' === trim($reference)) {
			$reference = NULL;
		}

		$options = $this->listPaymentOptions();
		$option = $options[$method_id];

		if (empty($option->amount)) {
			throw new UserException('Ce moyen de paiement ne peut pas être utilisé pour cette note');
		}
		elseif ($amount > $option->amount) {
			$a = $option->amount;
			throw new UserException(sprintf('Ce moyen de paiement ne peut être utilisé pour un montant supérieur à %d,%02d€', (int) ($a/100), (int) ($a%100)));
		}

		if (null !== $reference && $option->is_cash) {
			throw new UserException('Référence indiquée pour un règlement en espèces : vouliez-vous enregistrer un règlement par chèque ?');
		}

		return DB::getInstance()->insert(POS::tbl('tabs_payments'), [
			'tab'         => $this->id,
			'method'      => $method_id,
			'amount'      => $amount,
			'reference'   => $reference,
			'account'     => $option->account,
		]);
	}

	public function removePayment(int $id)
	{
		return DB::getInstance()->delete(POS::tbl('tabs_payments'), 'id = ? AND tab = ?', $id, $this->id);
	}

	public function listPayments()
	{
		return DB::getInstance()->get(POS::sql('SELECT tp.*,
			m.name AS method_name
			FROM @PREFIX_tabs_payments tp
			INNER JOIN @PREFIX_methods m ON m.id = tp.method
			WHERE tp.tab = ?;'), $this->id);
	}

	public function listPaymentOptions()
	{
		$remainder = $this->getRemainder();
		return DB::getInstance()->getGrouped(POS::sql('SELECT id, *,
			CASE
				WHEN max IS NOT NULL AND paid >= max THEN 0
				WHEN max IS NOT NULL AND payable > max THEN MIN(:left, max)
				WHEN min IS NOT NULL AND payable < min THEN 0
				ELSE MIN(:left, payable) END AS amount
			FROM (SELECT m.*, SUM(pt.amount) AS paid, SUM(i.qty * i.price) AS payable
				FROM @PREFIX_methods m
				INNER JOIN @PREFIX_products_methods pm ON pm.method = m.id
				INNER JOIN @PREFIX_tabs_items i ON i.product = pm.product AND i.tab = :id
				LEFT JOIN @PREFIX_tabs_payments AS pt ON pt.tab = i.tab AND m.id = pt.method
				GROUP BY m.id
			);'), ['id' => $this->id, 'left' => $remainder]);
	}

	static public function open(int $session_id)
	{
		$db = DB::getInstance();
		$db->insert(POS::tbl('tabs'), [
			'session' => $session_id,
		]);

		return $db->lastInsertRowID();
	}

	public function rename(string $new_name, ?int $user_id) {
		$new_name = trim($new_name);
		$db = DB::getInstance();
		return $db->update(POS::tbl('tabs'), ['name' => $new_name, 'user_id' => $user_id], $db->where('id', $this->id));
	}

	public function close() {
		$remainder = $this->getRemainder();

		if ($remainder != 0) {
			throw new UserException(sprintf("Impossible de clôturer la note: reste %s € à régler.", format_amount($remainder)));
		}

		return DB::getInstance()->preparedQuery(POS::sql('UPDATE @PREFIX_tabs SET closed = datetime(\'now\',\'localtime\') WHERE id = ?;'), [$this->id]);
	}

	public function reopen() {
		return DB::getInstance()->preparedQuery(POS::sql('UPDATE @PREFIX_tabs SET closed = NULL WHERE id = ?;'), [$this->id]);
	}

	public function delete() {
		$db = DB::getInstance();
		if ($db->count(POS::tbl('tabs_items'), 'tab = ?', $this->id) && $db->test(POS::tbl('tabs'), 'closed IS NULL AND id = ?', $this->id)) {
			throw new UserException('Impossible de supprimer une note qui n\'est pas close');
		}

		$db->delete(POS::tbl('tabs'), 'id = ?', $this->id);
	}

	static public function listForSession(int $session_id) {
		return DB::getInstance()->getGrouped(POS::sql('SELECT id, *, COALESCE((SELECT SUM(qty*price) FROM @PREFIX_tabs_items WHERE tab = @PREFIX_tabs.id), 0) AS total FROM @PREFIX_tabs WHERE session = ? ORDER BY closed IS NOT NULL, opened DESC;'), $session_id);
	}

	static public function searchMember($q) {
		$operator = 'LIKE';
		$identite = Config::getInstance()->get('champ_identite');

		if (is_numeric(trim($q)))
		{
			$column = 'numero';
			$operator = '=';
		}
		elseif (strpos($q, '@') !== false)
		{
			$column = 'email';
		}
		else
		{
			$column = $identite;
		}

		if ($operator == 'LIKE') {
			$q = str_replace(['%', '_'], ['\\%', '\\_'], $q);

			$q = '%' . $q . '%';
			$sql = sprintf('%s %s ? ESCAPE \'\\\'', $column, $operator);
		}
		else {
			$sql = sprintf('%s %s ?', $column, $operator);
		}

		$sql = sprintf('SELECT m.id, m.numero, m.email, m.%s AS identite,
			MAX(su.expiry_date) AS expiry_date,
			CASE WHEN su.expiry_date IS NULL THEN 0 WHEN su.expiry_date < date() THEN -1 WHEN su.expiry_date >= date() THEN 1 ELSE 0 END AS status
			FROM membres m
			LEFT JOIN services_users su ON su.id_user = m.id
			WHERE m.%s
			GROUP BY m.id
			ORDER BY m.%1$s COLLATE NOCASE LIMIT 0, 7;', $identite, $sql);

		return DB::getInstance()->get($sql, $q);
	}
}
