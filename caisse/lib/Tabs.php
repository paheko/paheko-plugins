<?php

namespace Paheko\Plugin\Caisse;

use Paheko\Config;
use Paheko\DB;
use Paheko\Users\DynamicFields;

use Paheko\Plugin\Caisse\Entities\Tab;
use KD2\DB\EntityManager as EM;

class Tabs
{
	static public function get(int $id): ?Tab
	{
		return EM::findOneById(Tab::class, $id);
	}

	static public function listForSession(int $session_id) {
		return DB::getInstance()->getGrouped(POS::sql('SELECT id, *, COALESCE((SELECT SUM(qty*price) FROM @PREFIX_tabs_items WHERE tab = @PREFIX_tabs.id), 0) AS total FROM @PREFIX_tabs WHERE session = ? ORDER BY closed IS NOT NULL, CASE WHEN closed IS NOT NULL THEN opened ELSE closed END DESC;'), $session_id);
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
			ORDER BY name COLLATE U_NOCASE LIMIT 0, 5;', $number_field, $email_field, $id_field, $sql);

		return $db->iterate($sql, $q);
	}

	static public function searchUserWithServices(string $q): \Generator
	{
		$db = DB::getInstance();
		foreach (self::searchUser($q) as $u) {
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
			yield $u;
		}
	}
}
