<?php

namespace Paheko\Plugin\Caisse;

use Paheko\DB;
use KD2\DB\EntityManager as EM;

class Products
{
	static public function listByCategory(bool $only_with_payment = true, bool $only_stockable = false): array
	{
		$db = DB::getInstance();

		$join = $only_with_payment ? 'INNER JOIN @PREFIX_products_methods pm ON pm.product = p.id INNER JOIN @PREFIX_methods m ON m.id = pm.method AND m.enabled = 1' : '';
		$where = $only_stockable ? 'AND p.stock IS NOT NULL' : '';

		// Don't select products that don't have any payment method linked: you wouldn't be able to pay for them
		$products = $db->get(POS::sql(sprintf('SELECT p.*, c.name AS category_name FROM @PREFIX_products p %s
			INNER JOIN @PREFIX_categories c ON c.id = p.category
			WHERE 1 %s
			GROUP BY p.id ORDER BY category_name COLLATE U_NOCASE, name COLLATE U_NOCASE;', $join, $where)));

		$list = [];

		foreach ($products as $product) {
			$cat = $product->category_name;
			$product->images_path = sprintf('p/public/%s/%d', 'caisse', $product->id);

			if (!array_key_exists($cat, $list)) {
				$list[$cat] = [];
			}

			$list[$cat][] = $product;
		}

		return $list;
	}

	static public function get(int $id): ?Entities\Product
	{
		return EM::findOneById(Entities\Product::class, $id);
	}

	static public function new(): Entities\Product
	{
		return new Entities\Product;
	}

	static public function getStatsPerMonth(int $year): array
	{
		$sql = 'SELECT strftime(\'%m\', i.added) AS month, i.added AS date, i.category_name, SUM(i.qty * i.price) AS sum, SUM(i.qty) AS count
			FROM @PREFIX_tabs_items i
			WHERE strftime(\'%Y\', i.added) = ? AND i.price > 0
			GROUP BY strftime(\'%m\', i.added), i.category_name
			ORDER BY month, i.category_name;';
		$sql = POS::sql($sql);

		return DB::getInstance()->get($sql, (string) $year);
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

	static public function checkUserWeightIsRequired(): bool
	{
		return DB::getInstance()->test(POS::tbl('products'), 'weight < 0');
	}
}
