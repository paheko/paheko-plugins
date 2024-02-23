<?php

namespace Paheko\Plugin\Caisse;

use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Utils;
use KD2\DB\EntityManager as EM;

use Paheko\Plugin\Caisse\Entities\Category;

class Categories
{
	static public function get(int $id): ?Category
	{
		return EM::findOneById(Category::class, $id);
	}

	static public function new(): Category
	{
		return new Category;
	}

	static public function list(): array
	{
		return EM::getInstance(Category::class)->all('SELECT * FROM @TABLE ORDER BY name;');
	}

	static public function listAssoc(): array
	{
		$db = DB::getInstance();
		return $db->getAssoc(POS::sql('SELECT id, name FROM @PREFIX_categories ORDER BY name;'));
	}

	static public function listSalesPerMonth(int $year): DynamicList
	{
		$columns = [
			'month' => [
				'label' => 'Mois',
				'select' => 'strftime(\'%Y-%m-01\', i.added)',
				'order' => 'i.added %s, i.category_name %1$s',
			],
			'category' => [
				'label' => 'Catégorie',
				'select' => 'i.category_name',
			],
			'count' => [
				'label' => 'Nombres de ventes',
				'select' => 'SUM(i.qty)',
			],
			'sum' => [
				'label' => 'Montant total',
				'select' => 'SUM(i.qty * i.price)',
			],
		];

		$list = POS::DynamicList($columns, '@PREFIX_tabs_items i', 'strftime(\'%Y\', i.added) = :year AND i.price > 0');
		$list->groupBy('strftime(\'%Y-%m\', i.added), i.category_name');
		$list->orderBy('month', false);
		$list->setParameter('year', (string)$year);
		$list->setTitle(sprintf('Ventes %d, par mois et par catégorie', $year));
		return $list;
	}


	static public function graphStatsPerMonth(int $year): string
	{
		$sql = 'SELECT * FROM (
			SELECT i.category_name AS name, CAST(strftime(\'%m\', i.added) AS INT) AS month, SUM(i.qty * i.price) / 100
			FROM @PREFIX_tabs_items i
			WHERE strftime(\'%Y\', i.added) = ? AND i.price > 0
			GROUP BY strftime(\'%m\', i.added), i.category_name
			UNION ALL
			SELECT \'Total\' AS name, CAST(strftime(\'%m\', i.added) AS INT) AS month, SUM(i.qty * i.price) / 100
			FROM @PREFIX_tabs_items i
			WHERE strftime(\'%Y\', i.added) = ? AND i.price > 0
			GROUP BY strftime(\'%m\', i.added)
			)
			ORDER BY name = \'Total\' DESC, name, month;';
		$sql = POS::sql($sql);

		$data = DB::getInstance()->getAssocMulti($sql, (string) $year, (string)$year);
		$empty = array_fill(1, 12, 0);

		foreach ($data as $key => &$value) {
			$value = array_replace($empty, $value);
		}

		unset($value);

		return POS::plotGraph(null, $data);
	}

	static public function graphStatsQtyPerMonth(int $year): string
	{
		$sql = 'SELECT * FROM (
			SELECT i.category_name AS name, CAST(strftime(\'%m\', i.added) AS INT) AS month,  SUM(i.qty)
			FROM @PREFIX_tabs_items i
			WHERE strftime(\'%Y\', i.added) = ?
			GROUP BY strftime(\'%m\', i.added), i.category_name
			UNION ALL
			SELECT \'\' AS name, 1 AS month, 0
			)
			ORDER BY name = \'\' DESC, name, month;';
		$sql = POS::sql($sql);

		$data = DB::getInstance()->getAssocMulti($sql, (string) $year);
		$empty = array_fill(1, 12, 0);

		foreach ($data as $key => &$value) {
			$value = array_replace($empty, $value);
		}

		unset($value);

		return POS::plotGraph(null, $data);
	}
}
