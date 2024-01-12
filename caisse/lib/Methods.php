<?php

namespace Paheko\Plugin\Caisse;

use Paheko\Config;
use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Utils;
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

	static public function listSalesPerMonth(int $year): DynamicList
	{
		$columns = [
			'month' => [
				'label' => 'Mois',
				'select' => 'strftime(\'%Y-%m-01\', p.date)',
				'order' => 'p.date %s, m.name %1$s',
			],
			'method' => [
				'label' => 'Méthode',
				'select' => 'm.name',
			],
			'count' => [
				'label' => 'Nombres de paiements',
				'select' => 'COUNT(p.id)',
			],
			'sum' => [
				'label' => 'Montant total',
				'select' => 'SUM(amount)',
			],
		];

		$tables = '@PREFIX_tabs_payments p INNER JOIN @PREFIX_methods m ON m.id = p.method';

		$list = POS::DynamicList($columns, $tables, 'strftime(\'%Y\', p.date) = :year AND amount > 0');
		$list->groupBy('strftime(\'%Y-%m\', p.date), m.id');
		$list->orderBy('month', false);
		$list->setParameter('year', (string)$year);
		$list->setTitle(sprintf('Paiements encaissés %d, par mois et par moyen de paiement', $year));
		return $list;
	}

	static public function listExitsPerMonth(int $year): DynamicList
	{
		$list = self::listSalesPerMonth($year);
		$list->setConditions('strftime(\'%Y\', p.date) = :year AND amount < 0');
		$list->setTitle(sprintf('Paiements décaissés %d, par mois et par moyen de paiement', $year));
		return $list;
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
