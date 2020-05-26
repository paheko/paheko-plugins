<?php

namespace Garradin\Plugin\Caisse;

use Garradin\DB;
use Garradin\Config;
use Garradin\UserException;

class Session
{
	public $id;

	public function __construct(int $id) {
		$this->id = $id;

		foreach (DB::getInstance()->first(POS::sql('SELECT * FROM @PREFIX_sessions WHERE id = ?;'), $id) as $key => $value) {
			$this->$key = $value;
		}
	}

	static public function open(int $user_id, int $amount): int
	{
		$db = DB::getInstance();
		$db->insert(POS::tbl('sessions'), [
			'open_user'   => $user_id,
			'open_amount' => $amount,
		]);

		return $db->lastInsertId();
	}

	static public function getCurrentId()
	{
		$db = DB::getInstance();
		return $db->firstColumn(POS::sql('SELECT id FROM @PREFIX_sessions WHERE closed IS NULL LIMIT 1;'));
	}

	static public function list()
	{
		$db = DB::getInstance();
		$name_field = Config::getInstance()->get('champ_identite');
		$sql = sprintf('SELECT s.*,
				m.%s AS open_user_name,
				m2.%1$s AS close_user_name
			FROM @PREFIX_sessions s
			LEFT JOIN membres m ON s.open_user = m.id
			LEFT JOIN membres m2 ON s.close_user = m2.id
			ORDER BY s.opened DESC;', $db->quoteIdentifier($name_field));
		return $db->get(POS::sql($sql));
	}


	public function hasOpenNotes() {
		return DB::getInstance()->test(POS::tbl('tabs'), 'session = ? AND closed IS NULL', $this->id);
	}

	public function close(int $user_id, int $amount, ?bool $confirm_error, array $payments)
	{
		$db = DB::getInstance();

		if ($this->hasOpenNotes()) {
			throw new UserException('Il y a des notes qui ne sont pas clôturées.');
		}

		$payments = array_map(function ($a) { return (int) $a; }, $payments);
		$payments = implode(',', $payments);

		$check_payments = $db->firstColumn(sprintf(POS::sql('SELECT COUNT(*) FROM @PREFIX_tabs_payments tp
			INNER JOIN @PREFIX_tabs t ON t.id = tp.tab AND t.session = ?
			INNER JOIN @PREFIX_methods m ON m.id = tp.method
			WHERE tp.id NOT IN (%s) AND m.is_cash = 0 LIMIT 1;'), $payments), $this->id);

		if ($check_payments) {
			throw new UserException('Certains paiements n\'ont pas été cochés comme vérifiés');
		}

		$expected_total = $this->getCashTotal() + $this->open_amount;

		if ($expected_total != $amount && !$confirm_error) {
			throw new UserException('Une erreur de caisse existe, il faut confirmer le recomptage de la caisse');
		}

		$db->begin();

		// Update items and payments for archival
		$db->preparedQuery(POS::sql('UPDATE @PREFIX_tabs_items AS ti
			SET (product, name, description, category_name) = (
				SELECT NULL, p.name, p.description, c.name
				FROM @PREFIX_products p
				INNER JOIN @PREFIX_categories c ON c.id = p.category
				WHERE p.id = ti.product)
			WHERE ti.tab IN (SELECT id FROM @PREFIX_tabs WHERE session = ?);'), [$this->id]);

		$db->preparedQuery(POS::sql('UPDATE @PREFIX_tabs_payments AS tp
			SET (method, method_name, is_cash) = (
				SELECT NULL, name, is_cash
				FROM @PREFIX_methods m
				WHERE m.id = tp.method)
			WHERE tp.tab IN (SELECT id FROM @PREFIX_tabs WHERE session = ?);'), [$this->id]);

		return $db->preparedQuery(POS::sql('UPDATE @PREFIX_sessions SET
			closed = datetime(\'now\', \'localtime\'),
			close_amount = ?,
			close_user = ?
			WHERE id = ?'), [$amount, $user_id, $this->id]) && $db->commit();
	}

	public function getTotal()
	{
		return DB::getInstance()->firstColumn(POS::sql('SELECT SUM(tp.amount) FROM @PREFIX_tabs_payments tp
			INNER JOIN @PREFIX_tabs t ON tp.tab = t.id AND t.session = ?'), $this->id);
	}

	public function listPayments()
	{
		return DB::getInstance()->get(POS::sql('SELECT tp.*,
			CASE WHEN tp.method IS NOT NULL THEN m.name ELSE tp.method_name END AS method_name
			FROM @PREFIX_tabs_payments tp
			INNER JOIN @PREFIX_tabs t ON tp.tab = t.id AND t.session = ?
			LEFT JOIN @PREFIX_methods m ON m.id = tp.method
			ORDER BY method_name, tp.date;'), $this->id);
	}

	public function listPaymentTotals()
	{
		return DB::getInstance()->get(POS::sql('SELECT SUM(tp.amount) AS total,
			CASE WHEN tp.method IS NOT NULL THEN m.name ELSE tp.method_name END AS method_name
			FROM @PREFIX_tabs_payments tp
			INNER JOIN @PREFIX_tabs t ON tp.tab = t.id AND t.session = ?
			LEFT JOIN @PREFIX_methods m ON m.id = tp.method
			GROUP BY method_name
			ORDER BY method_name;'), $this->id);
	}

	public function listTabsTotals()
	{
		return DB::getInstance()->get(POS::sql('SELECT *,
			(SELECT SUM(qty * price) FROM @PREFIX_tabs_items WHERE tab = t.id) AS total
			FROM @PREFIX_tabs t WHERE session = ? ORDER BY opened;'), $this->id);
	}

	public function listTabsWithItems()
	{
		$db = DB::getInstance();
		$tabs = $db->get(POS::sql('SELECT *, total - paid AS remainder
			FROM (SELECT *,
				(SELECT SUM(qty * price) FROM @PREFIX_tabs_items WHERE tab = t.id) AS total,
				(SELECT SUM(amount) FROM @PREFIX_tabs_payments WHERE tab = t.id) AS paid
				FROM @PREFIX_tabs t WHERE session = ? ORDER BY opened
			);'), $this->id);

		foreach ($tabs as &$tab) {
			$t = new Tab($tab->id, false);
			$tab->items = $t->listItems();
		}

		return $tabs;
	}

	public function listTotalsByCategory()
	{
		return DB::getInstance()->get(POS::sql('SELECT
			SUM(ti.qty * ti.price) AS total,
			CASE WHEN ti.product THEN c.name ELSE ti.category_name END AS cat_name
			FROM @PREFIX_tabs_items ti
			LEFT JOIN @PREFIX_products p ON ti.product = p.id
			LEFT JOIN @PREFIX_categories c ON c.id = p.category
			WHERE ti.tab IN (SELECT id FROM @PREFIX_tabs WHERE session = ?)
			GROUP BY cat_name;'), $this->id);

	}

	public function getCashTotal()
	{
		return DB::getInstance()->firstColumn(POS::sql('
			SELECT SUM(amount) FROM @PREFIX_tabs_payments p
			INNER JOIN @PREFIX_tabs t ON t.id = p.tab
			INNER JOIN @PREFIX_methods m ON m.id = p.method
			WHERE t.session = ? AND m.is_cash = 1;'), $this->id);
	}

	public function listPaymentWithoutCash()
	{
		return DB::getInstance()->get(POS::sql('
			SELECT p.* FROM @PREFIX_tabs_payments p
			INNER JOIN @PREFIX_tabs t ON t.id = p.tab
			INNER JOIN @PREFIX_methods m ON m.id = p.method
			WHERE t.session = ? AND m.is_cash = 0
			ORDER BY p.date;'), $this->id);
	}
}