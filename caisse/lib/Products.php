<?php

namespace Paheko\Plugin\Caisse;

use Paheko\DB;
use Paheko\DynamicList;
use KD2\DB\EntityManager as EM;

use Paheko\Plugin\Caisse\Entities\Product;

class Products
{
	static public function listBuyableByCategory(): array
	{
		$db = DB::getInstance();
		$sql = POS::sql('SELECT p.*, c.name AS category_name, c.id AS category_id
			FROM @PREFIX_products p
			INNER JOIN @PREFIX_products_methods pm ON pm.product = p.id
			INNER JOIN @PREFIX_methods m ON m.id = pm.method AND m.enabled = 1
			INNER JOIN @PREFIX_categories c ON c.id = p.category
			WHERE p.archived = 0
			GROUP BY p.id
			ORDER BY category_name COLLATE U_NOCASE, name COLLATE U_NOCASE;');

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
		return $db->get(POS::sql(sprintf('SELECT p.*, c.name AS category_name, p.stock * p.price AS sale_value, p.stock * p.purchase_price AS stock_value
			FROM @PREFIX_products p %s
			INNER JOIN @PREFIX_categories c ON c.id = p.category
			WHERE 1 %s
			GROUP BY p.id ORDER BY category_name COLLATE U_NOCASE, name COLLATE U_NOCASE;', $join, $where)));
	}

	/**
	 * Return list of products for stock view
	 */
	static public function getStockList(bool $archived = false, ?string $search = null): DynamicList
	{
		$list = self::getList($archived, $search);
		$list->addConditions(' AND p.stock IS NOT NULL');
		$list->addColumn('sale_value', ['select' => 'CASE WHEN p.stock >= 0 THEN p.stock * p.price ELSE NULL END', 'label' => 'Valeur à la vente']);
		$list->addColumn('stock_value', ['select' => 'p.stock * p.purchase_price', 'label' => 'Valeur du stock (à l\'achat)']);
		return $list;
	}

	/**
	 * Return list of products for management
	 */
	static public function getList(bool $archived = false, ?string $search = null): DynamicList
	{
		$columns = [
			'category' => [
				'select' => 'c.name',
				'label' => 'Catégorie',
				'order' => 'c.name COLLATE U_NOCASE %s, name COLLATE U_NOCASE %1$s',
			],
			'name' => [
				'select' => 'p.name',
				'label' => 'Nom',
			],
			'price' => [
				'select' => 'p.price',
				'label' => 'Prix unitaire',
			],
			'qty' => [
				'select' => 'p.qty',
				'label' => 'Quantité par défaut',
			],
			'id' => ['select' => 'p.id'],
			'id_category' => ['select' => 'p.category'],
			'stock' => ['select' => 'p.stock'],
			'num' => [
				'select' => 'p.id',
				'label' => 'Numéro unique',
				'export' => true,
			],
			'code' => [
				'select' => 'p.code',
				'label' => 'Code barre',
				'export' => true,
			],
			'description' => [
				'select' => 'p.description',
				'label' => 'Description',
				'export' => true,
			],
			'purchase_price' => [
				'select' => 'p.purchase_price',
				'label' => 'Prix d\'achat unitaire',
				'export' => true,
			],
			'stock2' => [
				'select' => 'p.stock',
				'label' => 'Stock',
				'export' => true,
			],
			'archived' => [
				'select' => 'CASE WHEN p.archived = 1 THEN \'Archivé\' END',
				'label' => 'Archivé',
				'export' => true,
			],
			'weight' => [
				'select' => sprintf('CASE p.weight WHEN NOT NULL THEN p.weight WHEN %d THEN \'Poids demandé\' WHEN %d THEN \'Prix au poids\' END', Product::WEIGHT_REQUIRED, Product::WEIGHT_BASED_PRICE),
				'label' => 'Poids',
				'export' => true,
			],
		];

		$conditions = 'p.archived = ' . (int) $archived;

		$search = is_string($search) ? trim($search) : null;
		$search = $search ?: null;

		if ($search !== null) {
			$conditions .= ' AND p.name LIKE :search ESCAPE \'\\\'';
		}

		$list = POS::DynamicList($columns, '@PREFIX_products p INNER JOIN @PREFIX_categories c ON c.id = p.category', $conditions);
		$list->orderBy('category', false);
		$list->setTitle('Liste des produits');

		if ($search !== null) {
			$db = DB::getInstance();
			$search = trim($search);
			$search = '%' . $db->escapeLike($search, '\\') . '%';
			$list->setParameter('search', $search);
		}

		return $list;
	}

	static public function getListForLinking(int $id, bool $archived = false, ?string $search = null): DynamicList
	{
		$list = self::getList($archived, $search);
		$list->addConditions(sprintf(POS::sql(' AND p.id NOT IN (SELECT id_product FROM @PREFIX_products_links) AND p.id != %d'), $id));
		$list->removeColumns(['archived', 'stock2', 'weight', 'purchase_price', 'code', 'description', 'num', 'stock', 'price', 'qty']);
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

	static public function listSales(int $year, string $period = 'year', ?int $location = null): DynamicList
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

		if ($location) {
			$list->addTables(POS::sql('INNER JOIN @PREFIX_tabs t ON t.id = i.tab INNER JOIN @PREFIX_sessions s ON s.id = t.session'));
			$list->addConditions(sprintf('AND s.id_location = %d', $location));
		}

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

	static public function markSelectedAsArchived(array $ids, bool $archived)
	{
		$ids = array_map('intval', $ids);
		$db = DB::getInstance();
		$db->exec(sprintf(POS::sql('UPDATE @PREFIX_products SET archived = %d WHERE %s;'), $archived, $db->where('id', $ids)));
	}

	static public function deleteSelected(array $ids)
	{
		$ids = array_map('intval', $ids);
		$db = DB::getInstance();
		$db->exec(sprintf(POS::sql('DELETE FROM @PREFIX_products WHERE %s;'), $db->where('id', $ids)));
	}
}
