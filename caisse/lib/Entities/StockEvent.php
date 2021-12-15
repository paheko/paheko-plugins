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
	protected string $label = '';
	protected int $type = 0;

	const TYPE_INVENTORY = 1;
	const TYPE_ORDER_RECEIVED = 2;

	const TYPES = [
		self::TYPE_INVENTORY => 'Inventaire',
		self::TYPE_ORDER_RECEIVED => 'Réception de commande',
	];

	public function __construct()
	{
		$this->date = new \DateTime;
		parent::__construct();
	}

	public function selfCheck(): void
	{
		$this->assert(trim($this->label) !== '', 'Le libellé ne peut rester vide.');
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
}