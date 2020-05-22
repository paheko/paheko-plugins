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
		$sql = sprintf('SELECT s.*, m.%s AS open_user_name
			FROM @PREFIX_sessions s
			INNER JOIN membres m ON s.open_user = m.id
			ORDER BY s.opened DESC;', $db->quoteIdentifier($name_field));
		return $db->get(POS::sql($sql));
	}

	public function close(int $amount)
	{
		$db = DB::getInstance();

		if ($db->test(POS::tbl('tabs'), 'session = ? AND closed IS NULL', $this->id)) {
			throw new UserException('Il y a des notes qui ne sont pas clôturées.');
		}

		return $db->preparedQuery(POS::sql('UPDATE @PREFIX_sessions SET
			closed = datetime(\'now\', \'localtime\'),
			close_amount = ?
			WHERE id = ?'), [$amount, $this->id]);
	}

	public function getTotal()
	{
		return DB::getInstance()->firstColumn(POS::sql('SELECT SUM(tp.amount) FROM @PREFIX_tabs_payments tp
			INNER JOIN @PREFIX_tabs t ON tp.tab = t.id AND t.session = ?'), $this->id);
	}

	public function listPayments()
	{
		return DB::getInstance()->get(POS::sql('SELECT tp.*, m.name
			FROM @PREFIX_tabs_payments tp
			INNER JOIN @PREFIX_tabs t ON tp.tab = t.id AND t.session = ?
			INNER JOIN @PREFIX_methods m ON m.id = tp.method
			ORDER BY m.name, tp.date;'), $this->id);
	}

	public function listPaymentTotals()
	{
		return DB::getInstance()->get(POS::sql('SELECT SUM(tp.amount) AS total, m.name FROM @PREFIX_tabs_payments tp
			INNER JOIN @PREFIX_tabs t ON tp.tab = t.id AND t.session = ?
			INNER JOIN @PREFIX_methods m ON m.id = tp.method
			GROUP BY tp.method
			ORDER BY m.name;'), $this->id);
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
			$t = new Tab($tab->id);
			$tab->items = $t->listItems();
		}

		return $tabs;
	}

	public function listTotalsByCategory()
	{
		return DB::getInstance()->get(POS::sql('SELECT
			SUM(ti.qty * ti.price) AS total,
			c.name
			FROM @PREFIX_tabs_items ti
			INNER JOIN @PREFIX_products p ON ti.product = p.id
			INNER JOIN @PREFIX_categories c ON c.id = p.category
			WHERE ti.tab IN (SELECT id FROM @PREFIX_tabs WHERE session = ?)
			GROUP BY c.id
			ORDER BY c.name;'), $this->id);

	}
}