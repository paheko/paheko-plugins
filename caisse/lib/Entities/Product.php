<?php

namespace Garradin\Plugin\Caisse\Entities;

use Garradin\Plugin\Caisse\POS;
use Garradin\Entity;
use Garradin\Utils;
use KD2\DB\EntityManager as EM;

class Product extends Entity
{
	const TABLE = POS::TABLES_PREFIX . 'products';

	protected int $id;
	protected int $category = 0;
	protected string $name = '';
	protected ?string $description = null;
	protected int $price = 0;
	protected int $qty = 1;
	protected ?int $stock = null;
	protected ?string $image = null;

	public function selfCheck(): void
	{
		$this->assert(trim($this->name) !== '', 'Le nom ne peut rester vide.');
		$this->assert($this->price >= 0);
		$this->assert($this->qty >= 0);

		$this->assert((bool) EM::findOneById(Category::class, $this->category), 'Catégorie invalide');
	}

	public function listPaymentMethods()
	{
		$sql = POS::sql('SELECT m.*, pm.method IS NOT NULL AS checked FROM @PREFIX_methods m
			LEFT JOIN @PREFIX_products_methods pm ON pm.method = m.id AND pm.product = ?
			ORDER BY m.name;');
		return EM::getInstance(Method::class)->DB()->get($sql, $this->exists() ? $this->id() : null);
	}

	public function importForm(array $source = null)
	{
		if (null === $source) {
			$source = $_POST;
		}

		if (isset($source['price'])) {
			$source['price'] = Utils::moneyToInteger($source['price']);
		}

		parent::importForm($source);
	}

	public function setMethods(array $methods)
	{
		$db = EM::getInstance(self::class)->DB();
		$db->begin();
		$sql = POS::sql('DELETE FROM @PREFIX_products_methods WHERE product = ?;');
		$db->preparedQuery($sql, $this->id());

		$sql = POS::sql('INSERT INTO @PREFIX_products_methods (product, method) VALUES (?, ?);');
		foreach ($methods as $id) {
			$db->preparedQuery($sql, $this->id(), (int) $id);
		}

		$db->commit();
	}

	public function history(bool $only_events = false): array
	{
		$events = $only_events ? ' AND h.event IS NOT NULL' : '';

		$db = EM::getInstance(self::class)->DB();
		$sql = sprintf(POS::sql('SELECT
			h.*, e.label AS event_label, e.type AS event_type, ti.tab
			FROM @PREFIX_products_stock_history h
			LEFT JOIN @PREFIX_stock_events e ON e.id = h.event AND e.applied = 1
			LEFT JOIN @PREFIX_tabs_items ti ON ti.id = h.item
			WHERE h.product = ? %s
			ORDER BY h.date DESC;'), $events);
		return $db->get($sql, $this->id());
	}
}