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

	static public function recalculateRemaining(?int $id_event = null): void
	{
		$where = '';

		if ($id_event) {
			$where = sprintf(' AND event != %d', $id_event);
		}

		$db = DB::getInstance();
		$db->begin();

		// Reset remaining value, the loop will only update rows that have been affected by a sale
		$sql = 'UPDATE @PREFIX_products_stock_history SET remaining = change
			WHERE event IN (SELECT id FROM @PREFIX_stock_events WHERE type = ?);';

		$db->preparedQuery(POS::sql($sql), StockEvent::TYPE_ORDER_RECEIVED);

		// Only iterate over sales and received orders, from newest to oldest
		$sql = 'SELECT h.id, h.product, h.event, h.change, h.price, h.remaining, h.date FROM @PREFIX_products_stock_history h
			INNER JOIN @PREFIX_stock_events e ON e.id = h.event
			WHERE e.type = ? ' . $where . '
			UNION ALL
			SELECT id, product, NULL AS event, change, NULL AS price, NULL AS remaining, date FROM @PREFIX_products_stock_history
			WHERE item IS NOT NULL
			ORDER BY date DESC, id DESC;';

		$consumed = [];

		echo '<pre>';
		print_r($db->get(POS::sql($sql), StockEvent::TYPE_ORDER_RECEIVED));

		foreach ($db->iterate(POS::sql($sql), StockEvent::TYPE_ORDER_RECEIVED) as $row) {
			var_dump($row); exit;
			$consumed[$row->product] ??= 0;

			if (!empty($row->item)) {
				$consumed[$row->product] += abs($row->change);
				continue;
			}
			// Product has not been consumed
			elseif ($consumed[$row->product] === 0) {
				continue;
			}

			$change = $consumed[$row->product];

			// We can't remove more items than we have
			if ($change > $row->remaining) {
				$change = $row->remaining;
			}

			$consumed[$row->product] -= $change;
			$row->remaining -= $change;

			$db->preparedQuery(POS::sql('UPDATE @PREFIX_products_stock_history SET remaining = ? WHERE id = ?;'), $row->remaining, $row->id);
		}

		$db->commit();
	}

	static public function updateRemainingForSession(?int $id_session = null): void
	{
		// Update current stock value
		$stock_sql = 'SELECT h.*, SUM(ti.qty) AS sold_qty FROM @PREFIX_products_stock_history h
			INNER JOIN @PREFIX_tabs_items ti ON ti.product = h.product
			INNER JOIN @PREFIX_products p ON p.id = ti.product
			INNER JOIN @PREFIX_tabs t ON t.id = ti.tab
			INNER JOIN @PREFIX_sessions s ON s.id = t.session
			WHERE s.closed IS NOT NULL
				AND s.id = ?
				AND h.remaining IS NOT NULL
				AND h.remaining > 0
				AND h.price IS NOT NULL
			GROUP BY h.id
			ORDER BY h.product, h.date, h.id;';

		$remaining = [];

		foreach ($db->iterate(POS::sql($stock_sql), $id_session) as $row) {
			$remaining[$row->product] ??= $row->sold_qty;

			if ($remaining[$row->product] === 0) {
				continue;
			}

			$consumed = min($row->remaining, $remaining[$row->product]);
			$remaining[$row->product] -= $consumed;

			$db->preparedQuery(POS::sql('UPDATE @PREFIX_products_stock_history SET remaining = ? WHERE id = ?;'), $row->remaining - $consumed, $row->id);
		}

	}

}
