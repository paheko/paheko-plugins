<?php

namespace Garradin\Plugin\Caisse;

use Garradin\DB;
use Garradin\UserException;

class Tab
{
	protected $id;

	public function __construct(int $id)
	{
		$this->id = $id;
	}

	public function getRemainder(): int
	{
		return (int) DB::getInstance()->firstColumn(POS::sql('SELECT
			(SELECT SUM(price * qty) FROM @PREFIX_tabs_items WHERE tab = ?)
			- COALESCE((SELECT SUM(amount) FROM @PREFIX_tabs_payments WHERE tab = ?), 0);'), $this->id, $this->id);
	}

	public function addItem(int $id)
	{
		$db = DB::getInstance();
		$product = $db->first(POS::sql('SELECT * FROM @PREFIX_products WHERE id = ?'), $id);

		return $db->insert(POS::tbl('tabs_items'), [
			'tab'     => $this->id,
			'product' => (int)$product->id,
			'qty'     => (int)$product->qty,
			'price'   => (int)$product->price,
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
		return DB::getInstance()->get(POS::sql('SELECT ti.*, p.name, p.description,
			ti.qty * ti.price AS total,
			GROUP_CONCAT(pm.method, \',\') AS methods,
			c.name AS category
			FROM @PREFIX_tabs_items ti
			INNER JOIN @PREFIX_products p ON ti.product = p.id
			INNER JOIN @PREFIX_products_methods pm ON pm.product = p.id
			INNER JOIN @PREFIX_categories c ON c.id = p.category
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

		if (empty($options[$method_id]->amount)) {
			throw new UserException('Ce moyen de paiement ne peut pas être utilisé pour cette note');
		}
		elseif ($amount > $options[$method_id]->amount) {
			$a = $options[$method_id]->amount;
			throw new UserException(sprintf('Ce moyen de paiement ne peut être utilisé pour un montant supérieur à %d,%02d€', (int) ($a/100), (int) ($a%100)));
		}

		return DB::getInstance()->insert(POS::tbl('tabs_payments'), [
			'tab'       => $this->id,
			'method'    => $method_id,
			'amount'    => $amount,
			'reference' => $reference,
		]);
	}

	public function removePayment(int $id)
	{
		return DB::getInstance()->delete(POS::tbl('tabs_payments'), 'id = ? AND tab = ?', $id, $this->id);
	}

	public function listPayments()
	{
		return DB::getInstance()->get(POS::sql('SELECT tp.*, m.name
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
				WHEN max IS NOT NULL AND payable > max THEN max
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

	public function rename(string $new_name) {
		$new_name = trim($new_name);
		$db = DB::getInstance();
		return $db->update(POS::tbl('tabs'), ['name' => $new_name], $db->where('id', $this->id));
	}

	public function close() {
		$remainder = $this->getRemainder();

		if ($remainder != 0) {
			throw new UserException(sprintf("Impossible de clôturer la note: reste %s € à régler.", format_amount($remainder)));
		}

		return DB::getInstance()->preparedQuery(POS::sql('UPDATE @PREFIX_tabs SET closed = datetime(\'now\',\'localtime\') WHERE id = ?;'), [$this->id]);
	}

	public function delete() {
		$db = DB::getInstance();
		if ($db->test(POS::tbl('tabs'), 'closed IS NULL AND id = ?', $this->id)) {
			throw new UserException('Impossible de supprimer une note qui n\'est pas close');
		}

		$db->delete(POS::tbl('tabs'), 'id = ?', $this->id);
	}

	static public function listForSession(int $session_id) {
		return DB::getInstance()->getGrouped(POS::sql('SELECT id, *, COALESCE((SELECT SUM(qty*price) FROM @PREFIX_tabs_items WHERE tab = @PREFIX_tabs.id), 0) AS total FROM @PREFIX_tabs WHERE session = ? ORDER BY closed IS NOT NULL, opened DESC;'), $session_id);
	}
}
