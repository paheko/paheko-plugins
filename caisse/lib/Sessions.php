<?php

namespace Garradin\Plugin\Caisse;

use Garradin\DB;
use Garradin\Config;
use KD2\DB\EntityManager as EM;

use Garradin\Plugin\Caisse\Entities\Session;

class Sessions
{
	static public function listYears(): array
	{
		return DB::getInstance()->getAssoc(POS::sql('SELECT strftime(\'%Y\', opened), strftime(\'%Y\', opened)
			FROM @PREFIX_sessions GROUP BY strftime(\'%Y\', opened);'));
	}

	static public function open(int $user_id, int $amount): Session
	{
		$session = new Session;
		$session->set('open_user', $user_id);
		$session->set('open_amount', $amount);
		$session->set('opened', new \DateTime);
		$session->save();
		return $session;
	}

	static public function getCurrentId(): ?int
	{
		$db = DB::getInstance();
		return $db->firstColumn(POS::sql('SELECT id FROM @PREFIX_sessions WHERE closed IS NULL LIMIT 1;'));
	}

	static public function getCurrent(): ?Session
	{
		return EM::findOne(Session::class, 'SELECT * FROM @TABLE WHERE closed IS NULL LIMIT 1;');
	}

	static public function get(int $id): ?Session
	{
		return EM::findOneById(Session::class, $id);
	}

	static public function list(): array
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
}