<?php

namespace Paheko\Plugin\Caisse;

use Paheko\DB;
use Paheko\DynamicList;
use KD2\DB\EntityManager as EM;

use Paheko\Plugin\Caisse\Entities\StockEvent;

class Stock
{
	const HISTORY_COLUMNS = [
		'date' => [
			'select' => 'h.date',
			'label' => 'Date',
		],
		'product_label' => [
			'select' => 'p.name',
			'label' => 'Produit',
		],
		'id_product' => [
			'select' => 'h.product',
		],
		'type' => [
			'select' => 'CASE
				WHEN h.item THEN \'Vente\'
				WHEN e.type = 0 THEN \'Événement\'
				WHEN e.type = 1 THEN \'Inventaire\'
				WHEN e.type = 2 THEN \'Réception commande\'
				ELSE \'?\' END
			',
			'label' => 'Type',
		],
		'event_label' => [
			'select' => 'e.label',
			'label' => 'Événement',
		],
		'change' => [
			'label' => 'Modification du stock',
			'select' => '(CASE WHEN e.type = 1 THEN \'=\' WHEN h.change > 0 THEN \'+\' ELSE \'\' END) || CAST(h.change AS TEXT)',
		],
		'id_tab' => ['select' => 'ti.tab'],
		'id_event' => ['select' => 'h.event'],
	];

	static public function get(int $id): ?StockEvent
	{
		return EM::findOneById(StockEvent::class, $id);
	}

	static public function new(): StockEvent
	{
		return new StockEvent;
	}

	static public function listEvents(): array
	{
		return EM::getInstance(StockEvent::class)->all('SELECT * FROM @TABLE ORDER BY date DESC;');
	}

	static public function listCategoriesValue(): array
	{
		$db = EM::getInstance(StockEvent::class)->DB();
		$list = $db->getGrouped(POS::sql('SELECT c.id, c.name AS label, SUM(p.stock) AS stock, SUM(CASE WHEN p.stock >= 0 THEN p.stock * p.price ELSE 0 END) AS sale_value, SUM(CASE WHEN p.stock >= 0 THEN p.stock * p.purchase_price ELSE 0 END) AS stock_value
			FROM @PREFIX_products p
			INNER JOIN @PREFIX_categories c ON c.id = p.category
			WHERE p.stock IS NOT NULL
			GROUP BY p.category;'));

		$total = (object) ['label' => 'Total', 'stock' => 0, 'sale_value' => 0, 'stock_value' => 0];

		foreach ($list as $row) {
			$total->stock_value += $row->stock_value;
			$total->sale_value += $row->sale_value;
			$total->stock += $row->stock;
		}

		$list['total'] = $total;
		return $list;
	}

	static public function getHistoryList(bool $only_events = false): DynamicList
	{
		$columns = self::HISTORY_COLUMNS;
		$conditions = '1';

		if ($only_events) {
			$conditions = 'h.event IS NOT NULL';
		}

		$tables = '@PREFIX_products_stock_history h
			INNER JOIN @PREFIX_products p ON p.id = h.product
			LEFT JOIN @PREFIX_stock_events e ON e.id = h.event AND e.applied = 1
			LEFT JOIN @PREFIX_tabs_items ti ON ti.id = h.item';

		$list = new DynamicList($columns, POS::sql($tables), $conditions);
		$list->orderBy('date', true);
		return $list;
	}

}
