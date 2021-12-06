<?php

namespace Garradin\Plugin\Caisse;

use Garradin\DB;
use Garradin\Utils;
use Garradin\Config;

class Method
{
	static public function getList(): array
	{
		return DB::getInstance()->getAssoc(POS::sql('SELECT id, name FROM @PREFIX_methods ORDER BY name;'));
	}

	static public function getStatsPerMonth(?int $year = null): array
	{
		$sql = 'SELECT strftime(\'%%Y-%%m\', p.date) AS month, date, m.name AS method, COUNT(p.id) AS count, SUM(amount) AS sum
			FROM @PREFIX_tabs_payments p
			INNER JOIN @PREFIX_methods m ON m.id = p.method
			%s
			GROUP BY strftime(\'%%Y-%%m\', p.date), m.id
			ORDER BY month, m.name;';
		$sql = sprintf($sql, $year ? 'WHERE strftime(\'%Y\', p.date) = ?' : '');
		$sql = POS::sql($sql);

		$args = [];

		if ($year) {
			$args[] = (string)$year;
		}

		return DB::getInstance()->getGrouped($sql, $args);
	}

	static public function graphStatsPerMonth(int $year): string
	{
		$sql = 'SELECT strftime(\'%m\', p.date) AS month, m.name, SUM(amount) / 100
			FROM @PREFIX_tabs_payments p
			INNER JOIN @PREFIX_methods m ON m.id = p.method
			WHERE strftime(\'%Y\', p.date) = ?
			GROUP BY strftime(\'%m\', p.date), m.id
			ORDER BY month, m.name;';
		$sql = POS::sql($sql);

		$data = DB::getInstance()->getAssocMulti($sql, (string) $year);
		return POS::barGraph(null, $data);
	}
}
