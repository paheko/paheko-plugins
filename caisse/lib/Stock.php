<?php

namespace Paheko\Plugin\Caisse;

use Paheko\DB;
use KD2\DB\EntityManager as EM;

use Paheko\Plugin\Caisse\Entities\StockEvent;

class Stock
{
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
		$list = $db->getGrouped(POS::sql('SELECT c.id, c.name AS label, COUNT(p.id) AS stock, SUM(p.stock * p.price) AS sale_value, SUM(p.stock * p.purchase_price) AS stock_value
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
}
