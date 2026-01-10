<?php

namespace Paheko\Plugin\Webmail;

use Paheko\DB;
use Paheko\Users\DynamicFields;

use Paheko\Plugin\Webmail\Entities\Account;

class Accounts
{
	static public function listWithUserNames(): array
	{
		$db = DB::getInstance();
		$sql = sprintf('SELECT a.id, a.address, %s AS name
			FROM %s a
			INNER JOIN users_search u ON u.id = a.id_user
			ORDER BY name COLLATE U_NOCASE;',
			DynamicFields::getNameFieldsSQL('u'),
			Account::TABLE,
		);
		return $db->get($sql);
	}

	static public function create(): Account
	{
		$a = new Account;
		return $a;
	}
}
