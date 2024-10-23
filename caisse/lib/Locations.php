<?php

namespace Paheko\Plugin\Caisse;

use Paheko\DynamicList;
use KD2\DB\EntityManager as EM;

use Paheko\Plugin\Caisse\Entities\Location;

class Locations
{
	static public function get(int $id): ?Location
	{
		return EM::findOneById(Location::class, $id);
	}

	static public function count(): int
	{
		return EM::getInstance(Location::class)->count();
	}

	static public function new(): Location
	{
		return new Location;
	}

	static public function getList(): DynamicList
	{
		$columns = [
			'id' => [],
			'name' => [
				'label' => 'Nom',
				'select' => 'name',
			],
		];


		$tables = '@PREFIX_locations';

		$list = POS::DynamicList($columns, $tables);
		$list->orderBy('name', false);
		return $list;
	}

	static public function listAssoc(): array
	{
		$list = [];
		$all = EM::getInstance(Location::class)->iterate('SELECT * FROM @TABLE ORDER BY name COLLATE U_NOCASE;');

		foreach ($all as $e) {
			$list[$e->id] = $e->name;
		}

		return $list;
	}
}
