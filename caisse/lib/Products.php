<?php

namespace Paheko\Plugin\Caisse;

use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Services\Services;
use KD2\DB\EntityManager as EM;

class Products
{
	static public function listBuyableByCategory(bool $enable_services, ?int $id_user = null): array
	{
		$db = DB::getInstance();

		$list = [];
		$list_single_fees = [
			'id'       => 's_all',
			'name'     => 'Activités',
			'products' => [],
		];

		// Prepend list of services
		if ($enable_services) {
			$services = Services::listGroupedWithFees($id_user, 1);

			foreach ($services as $service) {
				$service_products = [];

				foreach ($service->fees as $fee) {
					if (!$fee->id_account) {
						continue;
					}

					$service_products[] = [
						'id' => 'fee_' . $fee->id,
						'name' => $fee->label,
						'price' => $fee->user_amount ?? $fee->amount,
						'qty' => 1,
					];
				}

				$count = count($service_products);

				if (!$count) {
					continue;
				}

				if ($count === 1) {
					if ($service_products[0]['name'] !== $service->label) {
						$service_products[0]['name'] = $service->label . ' — ' . $service_products[0]['name'];
					}

					$list_single_fees['products'][] = $service_products[0];
				}
				else {
					$list['s_' . $service->id] = [
						'id'       => 's_' . $service->id,
						'name'     => $service->label,
						'products' => $service_products,
					];
				}
			}

			if (count($list_single_fees['products'])) {
				$list = ['s_all' => $list_single_fees] + $list;
			}
		}

		$sql = POS::sql('SELECT p.*, c.name AS category_name, c.id AS category_id
			FROM @PREFIX_products p
			INNER JOIN @PREFIX_products_methods pm ON pm.product = p.id
			INNER JOIN @PREFIX_methods m ON m.id = pm.method AND m.enabled = 1
			INNER JOIN @PREFIX_categories c ON c.id = p.category
			WHERE p.archived = 0
			GROUP BY p.id
			ORDER BY category_name COLLATE U_NOCASE, name COLLATE U_NOCASE;');

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
		return $db->get(POS::sql(sprintf('SELECT p.*, c.name AS category_name, p.stock * p.price AS sale_value, p.stock * p.purchase_price AS stock_value
			FROM @PREFIX_products p %s
			INNER JOIN @PREFIX_categories c ON c.id = p.category
			WHERE 1 %s
			GROUP BY p.id ORDER BY category_name COLLATE U_NOCASE, name COLLATE U_NOCASE;', $join, $where)));
	}

	static public function get(int $id): ?Entities\Product
	{
		return EM::findOneById(Entities\Product::class, $id);
	}

	static public function new(): Entities\Product
	{
		return new Entities\Product;
	}

	static public function createAndSaveForDebtAccount(string $account): Entities\Product
	{
		$db = DB::getInstance();
		$db->begin();
		$category_id = $db->firstColumn(POS::sql('SELECT id FROM @PREFIX_categories WHERE account = ?;'), $account);

		if (!$category_id) {
			$cat = Categories::new();
			$cat->importForm([
				'account' => $account,
				'name'    => 'Règlement d\'ardoise',
			]);

			$cat->save();
			$category_id = $cat->id();
		}

		$product = self::new();
		$product->importForm([
			'category' => $category_id,
			'name'     => 'Règlement d\'ardoise',
			'qty'      => 1,
			'archived' => true,
		]);

		$product->save();
		$product->enableAllMethodsExceptDebt();
		$db->commit();
		return $product;
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
				'select' => 'SUM(i.total)',
			],
			'weight' => [
				'label' => 'Poids total',
				'select' => 'SUM(i.qty * i.weight)',
			],
		];

		$list = POS::DynamicList($columns, '@PREFIX_tabs_items i', 'strftime(\'%Y\', i.added) = :year');
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
