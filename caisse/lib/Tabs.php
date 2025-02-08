<?php

namespace Paheko\Plugin\Caisse;

use Paheko\Config;
use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Users\DynamicFields;

use Paheko\Plugin\Caisse\Entities\Tab;
use Paheko\Plugin\Caisse\Entities\TabItem;
use KD2\DB\EntityManager as EM;

class Tabs
{
	static public function get(int $id): ?Tab
	{
		return EM::findOneById(Tab::class, $id);
	}

	static public function listForSession(int $session_id) {
		return DB::getInstance()->getGrouped(POS::sql('SELECT id, *, COALESCE((SELECT SUM(total) FROM @PREFIX_tabs_items WHERE tab = @PREFIX_tabs.id), 0) AS total FROM @PREFIX_tabs WHERE session = ? ORDER BY closed IS NOT NULL, CASE WHEN closed IS NOT NULL THEN opened ELSE closed END DESC;'), $session_id);
	}

	static public function listForUser(string $q): ?array
	{
		$db = DB::getInstance();
		$condition = 'name LIKE ? ESCAPE \'!\'';
		$params = ['%' . $db->escapeLike($q, '!') . '%'];

		foreach (self::searchUser($q) as $user) {
			$condition .= ' OR user_id = ?';
			$params[] = (int) $user->id;
			break;
		}

		$sql = sprintf(POS::sql('SELECT * FROM @PREFIX_tabs WHERE %s GROUP BY id ORDER BY opened DESC;'), $condition);

		return $db->get($sql, ...$params);
	}

	static public function searchUser(string $q): \Generator
	{
		$db = DB::getInstance();
		$operator = 'LIKE';
		$id_field = DynamicFields::getNameFieldsSQL('u');
		$number_field = 'u.' . $db->quoteIdentifier(DynamicFields::getNumberField());
		$email_field = 'u.' . $db->quoteIdentifier(DynamicFields::getFirstEmailField());

		if (is_numeric(trim($q)))
		{
			$column = $number_field;
			$operator = '=';
		}
		elseif (strpos($q, '@') !== false)
		{
			$column = $email_field;
		}
		else
		{
			$column = $id_field;
		}

		if ($operator == 'LIKE') {
			$q = str_replace(['%', '_'], ['\\%', '\\_'], $q);

			$q = '%' . $q . '%';
			$sql = sprintf('%s %s ? ESCAPE \'\\\'', $column, $operator);
		}
		else {
			$sql = sprintf('%s %s ?', $column, $operator);
		}

		// FIXME: use users_search
		$sql = sprintf('SELECT u.id, %s AS number, %s AS email, %s AS name
			FROM users u
			WHERE %s
			ORDER BY name COLLATE U_NOCASE LIMIT 0, 20;', $number_field, $email_field, $id_field, $sql);

		return $db->iterate($sql, $q);
	}

	static public function searchUserWithServices(string $q): array
	{
		$db = DB::getInstance();
		$users = self::searchUser($q);
		$out = [];

		foreach ($users as $u) {
			$u->services = $db->get('SELECT
					s.label,
					su.expiry_date,
					CASE
						WHEN su.expiry_date IS NULL THEN 1
						WHEN su.expiry_date < date() THEN -1
						WHEN su.expiry_date >= date() THEN 1
						ELSE 0
					END AS status
				FROM (SELECT *, MAX(expiry_date) AS expiry_date FROM services_users WHERE id_user = ? GROUP BY id_service) AS su
				INNER JOIN services s ON su.id_service = s.id
				WHERE s.end_date IS NULL OR s.end_date >= date()
				ORDER BY status DESC, s.label COLLATE U_NOCASE;', (int) $u->id);
			$out[] = $u;
		}

		return $out;
	}

	static public function getUnpaidDebtAmount(?int $user_id = null): int
	{
		if ($user_id !== null) {
			$join = 'INNER JOIN @PREFIX_tabs t ON t.id = p.tab';
			$where = sprintf(' AND t.user_id = %d', $user_id);
		}
		else {
			$join = $where = '';
		}

		$db = DB::getInstance();
		$sql = POS::sql(sprintf('SELECT SUM(p.amount)
			FROM @PREFIX_tabs_payments p %s
			WHERE p.status = %d %s;',
			$join,
			Tab::PAYMENT_STATUS_DEBT,
			$where
		));

		$due = (int) $db->firstColumn($sql);

		$sql = POS::sql(sprintf('SELECT SUM(p.total)
			FROM @PREFIX_tabs_items p %s
			WHERE p.type = %d %s;',
			$join,
			TabItem::TYPE_PAYOFF,
			$where
		));

		$paid = (int) $db->firstColumn($sql);

		return $due - $paid;
	}

	static public function listDebts(): ?DynamicList
	{
		$columns = [
			'date' => [
				'label' => 'Date',
			],
			'name' => [
				'label' => 'Nom',
			],
			'user_id' => [],
			'method' => [],
			'account' => [],
			'amount' => [
				'label' => 'Montant',
			],
		];

		$tables = '(
			SELECT MAX(t.opened) AS date, t.name, t.user_id, p.account, SUM(p.amount) - COALESCE((SELECT SUM(ti.total)
				FROM @PREFIX_tabs_items ti INNER JOIN @PREFIX_tabs tt ON ti.tab = tt.id
				WHERE ti.type = %d AND tt.user_id = t.user_id AND tt.name = t.name), 0) AS amount
			FROM @PREFIX_tabs t
			INNER JOIN @PREFIX_tabs_payments p ON p.tab = t.id
			LEFT JOIN @PREFIX_methods m ON p.method = m.id
			WHERE p.status = %d
			GROUP BY t.user_id, t.name)';

		$tables = POS::sql(sprintf($tables, TabItem::TYPE_PAYOFF, Tab::PAYMENT_STATUS_DEBT));

		$list = new DynamicList($columns, $tables, 'amount > 0');
		$list->orderBy('date', true);

		return $list;
	}

	static public function listDebtsHistory(?int $user_id = null): DynamicList
	{
		$columns = [
			'type' => ['label' => 'Type'],
			'date' => [
				'label' => 'Date',
			],
			'id' => [
				'label' => 'Note',
			],
			'name' => [
				'label' => 'Nom',
			],
			'user_id' => [],
			'method' => [],
			'account' => [],
			'amount' => [
				'label' => 'Montant',
			],
		];

		$tables = '(
			SELECT t.id, t.opened AS date, t.name, t.user_id, SUM(p.amount) AS amount, m.name AS method, \'debt\' AS type, p.account
			FROM @PREFIX_tabs t
			INNER JOIN @PREFIX_tabs_payments p ON p.tab = t.id
			LEFT JOIN @PREFIX_methods m ON p.method = m.id
			WHERE p.status = %d
			GROUP BY t.id
			UNION ALL
			SELECT t.id, t.opened AS date, t.name, t.user_id, SUM(ti.total) AS amount, NULL AS method, \'payoff\' AS type, ti.account
			FROM @PREFIX_tabs t
			INNER JOIN @PREFIX_tabs_items ti ON ti.tab = t.id
			WHERE ti.type = %d
			GROUP BY t.id)';

		$tables = POS::sql(sprintf($tables, Tab::PAYMENT_STATUS_DEBT, TabItem::TYPE_PAYOFF));
		$conditions = '1';

		if ($user_id) {
			$conditions = 'user_id = ' . (int)$user_id;
		}

		$list = new DynamicList($columns, $tables, $conditions);
		$list->orderBy('date', true);

		return $list;
	}
}
