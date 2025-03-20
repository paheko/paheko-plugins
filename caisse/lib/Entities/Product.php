<?php

namespace Paheko\Plugin\Caisse\Entities;

use Paheko\Plugin\Caisse\POS;
use Paheko\DB;
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
				throw new ValidationException('Code barre invalide. Vérifiez s\'il ne manque pas un chiffre. Le code barre doit comporter 13 chiffres.');
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

	public function getImagesPath(): string
	{
		return sprintf('p/public/%s/%d', 'caisse', $this->id());
	}
}
