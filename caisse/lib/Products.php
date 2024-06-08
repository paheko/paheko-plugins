<?php

namespace Paheko\Plugin\Caisse;

use Paheko\DB;
use Paheko\DynamicList;
use KD2\DB\EntityManager as EM;

class Products
{
	static public function listBuyableByCategory(): array
	{
		$db = DB::getInstance();
		$sql = POS::sql('SELECT p.*, c.name AS category_name, c.id AS category_id
			FROM @PREFIX_products p
			INNER JOIN @PREFIX_products_methods pm ON pm.product = p.id INNER JOIN @PREFIX_methods m ON m.id = pm.method AND m.enabled = 1
			INNER JOIN @PREFIX_categories c ON c.id = p.category
			GROUP BY p.id ORDER BY category_name COLLATE U_NOCASE, name COLLATE U_NOCASE;');

		$list = [];

		foreach ($db->iterate($sql) as $product) {
			$cat = $product->category_id;

			if (!array_key_exists($cat, $list)) {
				$list[$cat] = [
					'id'       => $product->category_id,
					'name'     => $product->category_name,
					'products' => [],
				];
			}

			$list[$cat]['products'][] = $product;
		}

		return $list;
	}


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

	static public function listSales(int $year, string $period = 'year'): DynamicList
	{
		$columns = [
			'name' => [
				'label' => 'Produit',
				'select' => 'i.name',
			],
			'count' => [
				'label' => 'Nombres de ventes',
				'select' => 'SUM(i.qty)',
			],
			'sum' => [
				'label' => 'Montant total',
				'select' => 'SUM(i.qty * i.price)',
			],
			'weight' => [
				'label' => 'Poids total',
				'select' => 'SUM(i.qty * i.weight)',
			],
		];

		$list = POS::DynamicList($columns, '@PREFIX_tabs_items i', 'strftime(\'%Y\', i.added) = :year AND i.price > 0', );
		$list->orderBy('count', true);
		$list->setParameter('year', (string)$year);
		$list->setTitle(sprintf('Ventes %d, par produit', $year));
		$list->groupBy('i.product');
		POS::applyPeriodToList($list, $period, 'i.added', 'i.id');

		// List all sales
		if ($period === 'all') {
			$columns['count']['label'] = 'Quantité';
			$columns['price'] = [
				'select' => 'i.price',
				'label' => 'Prix unitaire',
			];
			$columns['category'] = [
				'select' => 'i.category_name',
				'label' => 'Catégorie',
			];
			$columns['date'] = [
				'select' => 'i.added',
				'label'  => 'Date',
			];
			$columns['tab'] = [
				'select' => 'i.tab',
				'label'  => 'Note',
			];
			$list->setColumns($columns);
			$list->setModifier(function (&$row) {
				$row->date = new \DateTime($row->date);
			});
		}

		return $list;
	}

	static public function checkUserWeightIsRequired(): bool
	{
		return DB::getInstance()->test(POS::tbl('products'), 'weight < 0');
	}
}
