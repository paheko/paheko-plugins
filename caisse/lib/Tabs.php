<?php

namespace Paheko\Plugin\Caisse;

use Paheko\Config;
use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Users\DynamicFields;

use Paheko\Plugin\Caisse\Entities\Method;
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

	static public function getGlobalDebtBalance(): int
	{
		return self::requestBalance(Method::TYPE_DEBT);
	}

	static public function requestBalance(int $type, string $conditions = '1', ...$params): int
	{
		$db = DB::getInstance();
		$sql = self::getUserBalancesQuery($type);

		$sql = sprintf('SELECT SUM(amount) FROM (%s) WHERE %s;', $sql, $conditions);
		return (int) $db->firstColumn($sql, ...$params);
	}

	static public function getUserBalancesQuery(?int $type = null): string
	{
		$sql = '
			SELECT t.id, t.opened AS date, t.name, t.user_id, SUM(p.amount) * -1 AS amount, p.account, p.method AS id_method,
				m.name AS method, CASE WHEN p.type = %d THEN \'debt\' ELSE \'payment\' END AS type,
				1 AS is_settled
			FROM @PREFIX_tabs t
			INNER JOIN @PREFIX_tabs_payments p ON p.tab = t.id
			INNER JOIN @PREFIX_methods m ON p.method = m.id
			WHERE p.%s
			GROUP BY p.account, t.user_id, t.name, t.id
			UNION ALL
			SELECT t.id, t.opened AS date, t.name, t.user_id, SUM(ti.total) AS amount, ti.account, ti.id_method,
				m.name AS method, CASE WHEN ti.type = %d THEN \'payoff\' ELSE \'credit\' END AS type,
				CASE WHEN t.closed IS NULL THEN 0 ELSE 1 END AS is_settled
			FROM @PREFIX_tabs t
			INNER JOIN @PREFIX_tabs_items ti ON ti.tab = t.id
			INNER JOIN @PREFIX_methods m ON ti.id_method = m.id
			WHERE ti.%s
			GROUP BY ti.account, t.user_id, t.name, t.id
			ORDER BY date';

		$types = [];
		$tabtypes = [];

		if ($type === null || $type === Method::TYPE_DEBT) {
			$types[] = Method::TYPE_DEBT;
			$tabtypes[] = TabItem::TYPE_PAYOFF;
		}

		if ($type === null || $type === Method::TYPE_CREDIT) {
			$types[] = Method::TYPE_CREDIT;
			$tabtypes[] = TabItem::TYPE_CREDIT;
		}

		$db = DB::getInstance();
		$sql = sprintf(POS::sql($sql),
			Method::TYPE_DEBT,
			$db->where('type', 'IN', $types),
			TabItem::TYPE_PAYOFF,
			$db->where('type', 'IN', $tabtypes)
		);

		return $sql;
	}

	static public function listBalances(int $type): ?DynamicList
	{
		if ($type !== Method::TYPE_DEBT) {
			$type = Method::TYPE_CREDIT;
		}

		$columns = [
			'date' => [
				'label' => 'Date',
			],
			'name' => [
				'label' => 'Nom',
			],
			'user_id' => [],
			'id_method' => [],
			'amount' => [
				'label' => 'Solde',
				'select' => 'ABS(SUM(amount))',
			],
		];

		$tables = sprintf('(%s)', self::getUserBalancesQuery($type));

		$list = new DynamicList($columns, $tables);
		$list->orderBy('date', true);
		$list->groupBy('user_id, name' . ($type === Method::TYPE_DEBT ? ' HAVING SUM(amount) != 0' : ''));

		return $list;
	}

	static public function listBalancesHistory(int $type, ?int $user_id = null): DynamicList
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
			'amount' => [
				'label' => 'Montant',
				'select' => $type === Method::TYPE_DEBT ? 'amount * -1' : 'amount',
			],
			'method' => [
				'label' => 'Moyen de paiement',
			],
			'user_id' => [],
			'id_method' => [],
			'account' => [],
		];

		$tables = sprintf('(%s)', self::getUserBalancesQuery($type));
		$conditions = '1';

		if ($user_id) {
			$conditions = 'user_id = ' . (int)$user_id;
		}

		$list = new DynamicList($columns, $tables, $conditions);
		$list->orderBy('date', true);

		return $list;
	}


	static public function listStats(int $year, string $period = 'year', ?int $location = null): DynamicList
	{
		$columns = [
			'count' => [
				'label' => 'Nombre de notes',
				'select' => 'COUNT(*)',
			],
			'products_count' => [
				'label' => 'Nombre de produits',
				'select' => 'SUM(ti.qty)',
			],
			'price' => [
				'label' => 'Montant moyen d\'un produit',
				'select' => 'AVG(ti.price)',
			],
			'sum' => [
				'label' => 'Montant moyen de la note',
				'select' => 'SUM(ti.total)/COUNT(*)',
			],
			'avg_open_time' => [
				'label' => 'Heure d\'ouverture moyenne',
				'select' => 'AVG(strftime(\'%H.%M\', t.opened))',
			],
			'avg_close_time' => [
				'label' => 'Heure de fermeture moyenne',
				'select' => 'AVG(strftime(\'%H\', t.closed)+(strftime(\'%M\', t.closed)/60))',
			],
		];

		$list = POS::DynamicList($columns, '@PREFIX_tabs t INNER JOIN @PREFIX_tabs_items ti ON ti.tab = t.id', 'strftime(\'%Y\', t.opened) = :year AND t.closed IS NOT NULL AND ti.total > 0');
		$list->orderBy('count', true);
		//$list->groupBy('t.session');
		$list->setParameter('year', (string)$year);
		$list->setTitle(sprintf('Notes %d', $year));

		if ($period === 'all' || $period === 'day') {
			$columns['weekday'] = [
				'label' => 'Jour de la semaine',
				'select' => 'CASE strftime(\'%w\', t.opened)
					WHEN \'0\' THEN \'7-dimanche\'
					WHEN \'1\' THEN \'1-lundi\'
					WHEN \'2\' THEN \'2-mardi\'
					WHEN \'3\' THEN \'3-mercredi\'
					WHEN \'4\' THEN \'4-jeudi\'
					WHEN \'5\' THEN \'5-vendredi\'
					WHEN \'6\' THEN \'6-samedi\'
					END',
			];
		}

		$list->setColumns($columns);

		// List all sales
		if ($period === 'all') {
			$columns['date_short'] = [
				'select' => 'strftime(\'%d/%m/%Y\', t.opened)',
				'label'  => 'Date',
			];
			$columns['session'] = [
				'select' => 't.session',
				'label'  => 'Session',
			];
			$list->setColumns($columns);
			$list->orderBy('date_short', true);
		}
		POS::applyPeriodToList($list, $period, 't.opened', 't.session');

		if ($location) {
			$list->addTables(POS::sql('INNER JOIN @PREFIX_sessions s ON s.id = t.session'));
			$list->addConditions(sprintf('AND s.id_location = %d', $location));
		}

		return $list;
	}

}
