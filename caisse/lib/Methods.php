<?php

namespace Paheko\Plugin\Caisse;

use Paheko\DB;
use Paheko\Utils;
use Paheko\Config;
use KD2\DB\EntityManager as EM;

use Paheko\Plugin\Caisse\Entities\Method;

class Methods
{
	static public function get(int $id): ?Method
	{
		return EM::findOneById(Method::class, $id);
	}

	static public function new(): Method
	{
		$m = new Method;
		$m->enabled = true;
		return $m;
	}

	static public function list(): array
	{
		return EM::getInstance(Method::class)->all('SELECT * FROM @TABLE ORDER BY name;');
	}

	static public function getStatsPerMonth(?int $year = null, bool $cash_out = false): array
	{
		$sql = 'SELECT strftime(\'%%Y-%%m\', p.date) AS month, date, m.name AS method, COUNT(p.id) AS count, SUM(amount) AS sum
			FROM @PREFIX_tabs_payments p
			INNER JOIN @PREFIX_methods m ON m.id = p.method
			WHERE 1 %s %s
			GROUP BY strftime(\'%%Y-%%m\', p.date), m.id
			ORDER BY month, m.name;';
		$sql = sprintf($sql, $year ? 'AND strftime(\'%Y\', p.date) = ?' : '', $cash_out ? 'AND amount < 0' : 'AND amount > 0');
		$sql = POS::sql($sql);

		$args = [];

		if ($year) {
			$args[] = (string)$year;
		}

		return DB::getInstance()->get($sql, $args);
	}

	static public function graphStatsPerMonth(int $year): string
	{
		$sql = 'SELECT * FROM (
			SELECT m.name AS name, CAST(strftime(\'%m\', p.date) AS INT) AS month, SUM(amount) / 100
			FROM @PREFIX_tabs_payments p
			INNER JOIN @PREFIX_methods m ON m.id = p.method
			WHERE strftime(\'%Y\', p.date) = ? AND amount > 0
			GROUP BY strftime(\'%m\', p.date), m.id
			UNION ALL
			SELECT \'Total\' AS name, CAST(strftime(\'%m\', p.date) AS INT) AS month, SUM(amount) / 100
			FROM @PREFIX_tabs_payments p
			WHERE strftime(\'%Y\', p.date) = ? AND amount > 0
			GROUP BY strftime(\'%m\', p.date)
			)
			ORDER BY name = \'Total\' DESC, name;';
		$sql = POS::sql($sql);

		$data = DB::getInstance()->getAssocMulti($sql, (string) $year, (string) $year);
		$empty = array_fill(1, 12, 0);

		foreach ($data as $key => &$value) {
			$value = array_replace($empty, $value);
		}

		unset($value);

		return POS::plotGraph(null, $data);
	}
}
