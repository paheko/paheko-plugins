<?php

namespace Garradin\Plugin\Caisse;

use Garradin\DB;

class Session
{
	public function open(int $user_id, int $amount): int
	{
		$db = DB::getInstance();
		$db->insert(POS::tbl('sessions'), [
			'open_user'   => $user_id,
			'open_amount' => $amount,
		]);

		return $db->lastInsertId();
	}

	public function getCurrent()
	{
		$db = DB::getInstance();
		return $db->first(POS::sql('SELECT * FROM @PREFIX_sessions WHERE closed IS NULL LIMIT 1;'));
	}

	public function close(int $id, int $amount)
	{
		$db = DB::getInstance();

		if ($db->test(POS::tbl('tabs'), 'session = ? AND closed IS NULL', $id)) {
			throw new UserException('Il y a des notes qui ne sont pas clôturées.');
		}
	}

	public function listPayments(int $id)
	{
		return DB::getInstance()->get(POS::sql('SELECT tp.*, m.name
			FROM @PREFIX_tabs_payments tp
			INNER JOIN @PREFIX_tabs t ON tp.tab = t.id AND t.session = ?
			INNER JOIN @PREFIX_methods m ON m.id = tp.method
			ORDER BY m.name, tp.date;', $id));
	}

	public function listPaymentTotals(int $id)
	{
		return DB::getInstance()->get(POS::sql('SELECT SUM(tp.amount), m.name FROM @PREFIX_tabs_payments tp
			INNER JOIN @PREFIX_tabs t ON tp.tab = t.id AND t.session = ?
			INNER JOIN @PREFIX_methods m ON m.id = tp.method
			GROUP BY tp.method
			ORDER BY m.name;', $id));
	}

	public function listTabsTotals(int $id)
	{
		return DB::getInstance()->get(POS::sql('SELECT *,
			(SELECT SUM(qty * price) FROM @PREFIX_tabs_items WHERE tab = t.id) AS total
			FROM @PREFIX_tabs t WHERE session = ? AND closed IS NULL ORDER BY opened;', $id));
	}
}