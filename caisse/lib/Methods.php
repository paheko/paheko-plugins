<?php

namespace Paheko\Plugin\Caisse;

use Paheko\Config;
use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Utils;
use KD2\DB\EntityManager as EM;

use Paheko\Plugin\Caisse\Entities\Method;
use Paheko\Plugin\Caisse\Entities\Tab;

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

	static public function getList(bool $with_location): DynamicList
	{
		$columns = [
			'id' => ['select' => 'm.id'],
			'name' => [
				'label' => 'Nom',
				'select' => 'm.name',
			],
			'location' => [
				'label' => 'Lieu',
				'select' => 'CASE WHEN id_location IS NULL THEN NULL ELSE l.name END',
			],
			'type' => [
				'label' => 'Type',
			],
			'account' => [
				'label' => 'Compte',
			],
			'enabled' => [
				'label' => 'Activé',
			],
		];

		if (!$with_location) {
			unset($columns['location']);
		}

		$tables = '@PREFIX_methods m LEFT JOIN @PREFIX_locations l ON l.id = m.id_location';

		$list = POS::DynamicList($columns, $tables);
		$list->orderBy('name', false);
		$list->setModifier(function (&$row) {
			$row->type = Method::TYPES_LABELS[$row->type];
		});
		return $list;
	}

	static public function listSales(int $year, string $period = 'year'): DynamicList
	{
		$columns = [
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
		$list->groupBy('m.id');
		$list->orderBy('method', true);
		$list->setParameter('year', (string)$year);
		$list->setTitle(sprintf('Paiements encaissés %d, par moyen de paiement', $year));
		POS::applyPeriodToList($list, $period, 'p.date', 'p.id');

		// List all sales
		if ($period === 'all') {
			unset($columns['count']);
			$columns['sum'] = [
				'select' => 'amount',
				'label' => 'Montant',
			];
			$columns['reference'] = [
				'select' => 'p.reference',
				'label' => 'Référence de paiement',
			];
			$columns['date'] = [
				'select' => 'p.date',
				'label'  => 'Date',
			];
			$columns['tab'] = [
				'select' => 'p.tab',
				'label'  => 'Note',
			];
			$list->setColumns($columns);
			$list->setModifier(function (&$row) {
				$row->date = new \DateTime($row->date);
			});
		}

		return $list;
	}

	static public function listExits(int $year, string $period = 'year'): DynamicList
	{
		$list = self::listSales($year, $period);
		$list->setConditions('strftime(\'%Y\', p.date) = :year AND amount < 0');
		$list->setTitle(sprintf('Paiements décaissés %d, par moyen de paiement', $year));
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
