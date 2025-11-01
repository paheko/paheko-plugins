<?php

namespace Paheko\Plugin\Caisse\Entities;

use Paheko\Plugin\Caisse\Categories;
use Paheko\Plugin\Caisse\POS;
use Paheko\Plugin\Caisse\Stock;
use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Entity;
use Paheko\Utils;
use Paheko\ValidationException;
use KD2\DB\EntityManager as EM;
use KD2\Graphics\BarCode;

class Product extends Entity
{
	const TABLE = POS::TABLES_PREFIX . 'products';

	protected ?int $id;
	protected int $category = 0;
	protected string $name = '';
	protected ?string $description = null;
	protected int $price = 0;
	protected ?int $purchase_price = null;
	protected int $qty = 1;
	protected ?int $stock = null;
	protected ?int $weight = null;
	protected ?string $image = null;
	protected ?string $code = null;
	protected bool $archived = false;

	protected ?int $id_fee = null;

	protected ?Category $_category = null;

	const WEIGHT_DISABLED = null;
	const WEIGHT_REQUIRED = -1;
	const WEIGHT_BASED_PRICE = -2;

	public function selfCheck(): void
	{
		$this->assert(trim($this->name) !== '', 'Le nom ne peut rester vide.');
		$this->assert($this->qty >= 0, 'La quantité doit être supérieure ou égale à zéro.');
		$this->assert($this->weight === self::WEIGHT_DISABLED
			|| $this->weight === self::WEIGHT_REQUIRED
			|| $this->weight === self::WEIGHT_BASED_PRICE
			|| $this->weight > 0, 'Le poids doit être vide ou supérieur à zéro.');
		$this->assert($this->purchase_price === null || $this->purchase_price > 0, 'Le prix d\'achat doit être vide ou supérieur à zéro.');

		$this->assert((bool) EM::findOneById(Category::class, $this->category), 'Catégorie invalide');
	}

	public function listPaymentMethods()
	{
		$value = $this->exists() ? 'pm.method IS NOT NULL' : '1';
		$sql = POS::sql(sprintf('SELECT m.*, %s AS checked FROM @PREFIX_methods m
			LEFT JOIN @PREFIX_products_methods pm ON pm.method = m.id AND pm.product = ?
			ORDER BY m.name;', $value));
		return EM::getInstance(Method::class)->DB()->get($sql, $this->exists() ? $this->id() : null);
	}

	public function importForm(?array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['price'])) {
			$source['price'] = Utils::moneyToInteger($source['price']);
		}

		if (isset($source['purchase_price'])) {
			$source['purchase_price'] = Utils::moneyToInteger($source['purchase_price']) ?: null;
		}

		if (!empty($source['weight_based_price'])) {
			$source['weight'] = self::WEIGHT_BASED_PRICE;
		}
		elseif (!empty($source['weight_required'])) {
			$source['weight'] = self::WEIGHT_REQUIRED;
		}
		elseif (isset($source['weight'])) {
			$source['weight'] = Utils::weightToInteger($source['weight']);
		}

		if (!empty($source['code'])) {
			$code = new BarCode($source['code']);

			if (!$code->verify()) {
				throw new ValidationException('Code barre invalide. Vérifiez s\'il ne manque pas un chiffre. Le code barre doit comporter 8 ou 13 chiffres.');
			}

			$source['code'] = $code->get();
		}

		if (isset($source['archived_present'])) {
			$source['archived'] = boolval($source['archived'] ?? false);
		}

		parent::importForm($source);
	}

	public function getSVGBarcode(): string
	{
		if (!$this->code) {
			return '';
		}

		$code = new BarCode($this->code);
		return $code->toSVG();
	}

	public function setMethods(array $methods): void
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

	public function enableAllMethodsExceptDebt(): void
	{
		$sql = POS::sql('INSERT INTO @PREFIX_products_methods (product, method) SELECT ?, id FROM @PREFIX_methods WHERE type != ?;');
		DB::getInstance()->preparedQuery($sql, $this->id(), Method::TYPE_DEBT);
	}

	public function getHistoryList(bool $only_events = false): DynamicList
	{
		$columns = Stock::HISTORY_COLUMNS;
		unset($columns['product_label']);

		$conditions = 'h.product = ' . (int)$this->id();

		if ($only_events) {
			$conditions .= ' AND h.event IS NOT NULL';
		}

		$tables = '@PREFIX_products_stock_history h
			LEFT JOIN @PREFIX_stock_events e ON e.id = h.event AND e.applied = 1
			LEFT JOIN @PREFIX_tabs_items ti ON ti.id = h.item';

		$list = new DynamicList($columns, POS::sql($tables), $conditions);
		$list->orderBy('date', true);
		return $list;
	}

	public function getImagesPath(): string
	{
		return sprintf('p/public/%s/%d', 'caisse', $this->id());
	}

	public function category(): Category
	{
		$this->_category ??= Categories::get($this->category);
		return $this->_category;
	}

	public function isLinked(): bool
	{
		if (!$this->exists()) {
			return false;
		}

		return EM::getInstance(self::class)->DB()->test(POS::SQL('@PREFIX_products_links'), 'id_linked_product = ?', $this->id());
	}

	public function listLinkedProducts(): array
	{
		if (!$this->exists()) {
			return [];
		}

		return EM::getInstance(self::class)->all(POS::sql('SELECT p.*
			FROM @TABLE AS p
			INNER JOIN @PREFIX_products_links AS d ON p.id = d.id_linked_product
			WHERE d.id_product = ?
			GROUP BY p.id;'), $this->id());
	}

	public function listLinkedProductsAssoc(): array
	{
		if (!$this->exists()) {
			return [];
		}

		return EM::getInstance(self::class)->DB()->getAssoc(POS::sql('SELECT p.id, p.name
			FROM @PREFIX_products AS p
			INNER JOIN @PREFIX_products_links AS d ON p.id = d.id_linked_product
			WHERE d.id_product = ?
			GROUP BY p.id;'), $this->id());
	}

	public function setLinkedProducts(array $ids): void
	{
		$db = EM::getInstance(self::class)->DB();
		$db->begin();

		$db->preparedQuery(POS::sql('DELETE FROM @PREFIX_products_links WHERE id_product = ?;'), $this->id());

		foreach ($ids as $id) {
			$db->insert(POS::tbl('products_links'), ['id_product' => $this->id(), 'id_linked_product' => (int)$id]);
		}

		$db->commit();

	}
}
