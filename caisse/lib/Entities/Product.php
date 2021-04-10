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
}