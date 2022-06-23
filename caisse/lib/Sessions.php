<?php

namespace Garradin\Plugin\Caisse;

use Garradin\Config;
use Garradin\DB;
use Garradin\DynamicList;
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

	static public function list(): DynamicList
	{
		$db = DB::getInstance();
		$name_field = Config::getInstance()->get('champ_identite');

		$columns = [
			'id' => [
				'select' => 's.id',
				'label' => 'Num.',
			],
			'opened' => [
				'label' => 'Ouverture',
				'select' => 's.opened',
			],
			'open_user_name' => [
				'select' => 'm.' . $db->quoteIdentifier($name_field),
			],
			'open_amount' => [
				'label' => 'Montant',
				'order' => null,
			],
			'closed' => [
				'label' => 'Clôture',
				'select' => 's.closed',
			],
			'closed_same_day' => [
				'select' => 'date(s.closed) = date(s.opened)',
			],
			'close_user_name' => [
				'select' => 'm2.' . $db->quoteIdentifier($name_field),
			],
			'close_amount' => [
				'label' => 'Montant clôture',
				'order' => null,
			],
			'error_amount' => [
				'label' => 'Erreur',
			],
			'total' => [
				'label' => 'Recettes',
				'select' => 'SUM(ti.qty * ti.price)',
				'order' => null,
			],
		];

		$tables = '@PREFIX_sessions s
			LEFT JOIN @PREFIX_tabs t ON t.session = s.id
			LEFT JOIN @PREFIX_tabs_items ti ON ti.tab = t.id
			LEFT JOIN membres m ON s.open_user = m.id
			LEFT JOIN membres m2 ON s.close_user = m2.id';

		$tables = POS::sql($tables);

		$list = new DynamicList($columns, $tables);
		$list->orderBy('opened', true);
		$list->groupBy('s.id');
		return $list;
	}
}