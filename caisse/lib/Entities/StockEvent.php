<?php

namespace Garradin\Plugin\Caisse\Entities;

use Garradin\Entity;
use Garradin\Plugin\Caisse\POS;
use KD2\DB\EntityManager as EM;

class StockEvent extends Entity
{
	const TABLE = POS::TABLES_PREFIX . 'stock_events';

	protected int $id;
	protected \DateTime $date;
	protected int $type = 0;
	protected string $label = '';
	protected bool $applied = false;

	const TYPE_OTHER = 0;
	const TYPE_INVENTORY = 1;
	const TYPE_ORDER_RECEIVED = 2;

	const TYPES = [
		self::TYPE_INVENTORY => 'Inventaire',
		self::TYPE_ORDER_RECEIVED => 'Réception de commande',
		self::TYPE_OTHER => 'Autre',
	];

	public function __construct()
	{
		$this->date = new \DateTime;
		parent::__construct();
	}

	public function selfCheck(): void
	{
		$this->assert(trim($this->label) !== '', 'Le libellé ne peut rester vide.');
		$this->assert(array_key_exists($this->type, self::TYPES));
	}

	public function applyChanges(): void
	{
		$this->set('applied', true);

		$db = EM::getInstance(self::class)->DB();

		if ($this->type == $this::TYPE_INVENTORY) {
			/*
			// Reset stock to zero in history
			// This is necessary as we must be able to reconstruct the current stock from the history
			$sql = sprintf('
				INSERT INTO @PREFIX_products_stock_history (product, change, date)
					SELECT p.id, stock * -1, datetime(\'now\', \'localtime\')
					FROM @PREFIX_products p
					INNER JOIN @PREFIX_products_stock_history h ON h.product = p.id AND h.event = %d;
			');
			*/
			$sql = sprintf('
				-- Set product stock to inventory
				UPDATE @PREFIX_products AS p
					SET stock = (SELECT change FROM @PREFIX_products_stock_history AS h WHERE h.product = p.id AND h.event = %1$d)
					WHERE id IN (SELECT product FROM @PREFIX_products_stock_history AS h WHERE h.event = %1$d);
			', $this->id());
		}
		else {
			// Just update current stock
			$sql = sprintf('
				UPDATE @PREFIX_products AS p
					SET stock = stock + (SELECT change FROM @PREFIX_products_stock_history AS h WHERE h.product = p.id AND h.event = %1$d)
					WHERE id IN (SELECT product FROM @PREFIX_products_stock_history AS h WHERE h.event = %1$d);
			', $this->id());
		}

		$sql .= sprintf('UPDATE @PREFIX_products_stock_history SET date = datetime(\'now\', \'localtime\') WHERE event = %d;', $this->id());

		$db->exec(POS::sql($sql));
		$this->save();
	}

	public function listChanges(): array
	{
		$db = EM::getInstance(self::class)->DB();

		$sql = POS::sql('SELECT
			c.name AS category_name, p.name AS product_name, p.stock AS current_stock,
			h.change AS change, p.id AS product_id
			FROM @PREFIX_products_stock_history h
			LEFT JOIN @PREFIX_products p ON p.id = h.product
			LEFT JOIN @PREFIX_categories c ON c.id = p.category
			WHERE event = ?
			ORDER BY c.name, p.name;');

		return $db->get($sql, $this->id());
	}

	public function delete(): bool
	{
		// Reverse stock change
		if ($this->applied) {
			$db = EM::getInstance(self::class)->DB();

			$sql = sprintf('UPDATE @PREFIX_products AS p SET stock = stock + (SELECT change FROM @PREFIX_products_stock_history h WHERE h.product = p.id AND h.event = %d) * -1 WHERE id IN (SELECT product FROM @PREFIX_products_stock_history WHERE event = %1$d);', $this->id());
			$sql = POS::sql($sql);

			$db->exec($sql);
		}

		return parent::delete();
	}

	public function addProduct(int $id, int $qty = 0): ProductStockHistory
	{
		$p = EM::findOne(ProductStockHistory::class,
			POS::sql('SELECT * FROM @PREFIX_products_stock_history WHERE product = ? AND event = ?;'), $id, $this->id());

		if ($p) {
			return $p;
		}

		$p = new ProductStockHistory;
		$p->product = $id;
		$p->event = $this->id();
		$p->date = new \DateTime;
		$p->change = $qty;
		$p->save();
		return $p;
	}


	public function deleteProduct(int $id): void
	{
		$p = EM::findOne(ProductStockHistory::class,
			POS::sql('SELECT * FROM @PREFIX_products_stock_history WHERE product = ? AND event = ?;'), $id, $this->id());

		if (!$p) {
			throw new \InvalidArgumentException('Product is not in stock event');
		}

		$p->delete();
	}

	public function setProductQty(int $id, int $qty)
	{
		$p = EM::findOne(ProductStockHistory::class,
			POS::sql('SELECT * FROM @PREFIX_products_stock_history WHERE product = ? AND event = ?;'), $id, $this->id());

		if (!$p) {
			throw new \InvalidArgumentException('Product is not in stock event');
		}

		if ($this->type == self::TYPE_INVENTORY) {
			$qty = abs($qty);
		}

		$p->change = $qty;
		$p->save();
	}
}