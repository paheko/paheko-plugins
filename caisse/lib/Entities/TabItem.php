<?php

namespace Paheko\Plugin\Caisse\Entities;

use Paheko\DB;
use Paheko\UserException;

use Paheko\Plugin\Caisse\POS;
use Paheko\Plugin\Caisse\Products;
use Paheko\Plugin\Caisse\Tabs;
use Paheko\Entity;
use Paheko\Utils;
use Paheko\ValidationException;

class TabItem extends Entity
{
	const TABLE = POS::TABLES_PREFIX . 'tabs_items';

	protected ?int $id;
	protected int $tab;
	protected \DateTime $added;
	protected ?int $product = null;
	protected int $qty;
	protected int $price;
	protected ?int $weight = null;
	protected int $total;
	protected string $name;
	protected string $category_name;
	protected ?string $description = null;
	protected ?string $account = null;
	protected int $type;
	protected int $pricing = self::PRICING_QTY;

	protected ?int $id_fee = null;
	protected ?int $id_subscription = null;

	/**
	 * This is for when we have an item and children items, ie. an annual subscription
	 * bringing a mandatory federal fee
	 */
	protected ?int $id_parent_item = null;

	/**
	 * Used for payoff/credit payments, to link item with method
	 */
	protected ?int $id_method = null;

	protected ?Product $_product = null;

	const TYPE_PRODUCT = 0;
	const TYPE_PAYOFF = 1;
	const TYPE_CREDIT = 2;

	const PRICING_QTY = 0;
	const PRICING_QTY_WEIGHT = 1;
	const PRICING_SINGLE = 2;

	public function selfCheck(): void
	{
		$this->assert(in_array($this->type, [self::TYPE_PRODUCT, self::TYPE_PAYOFF, self::TYPE_CREDIT], true));
		$this->assert(in_array($this->pricing, [self::PRICING_QTY, self::PRICING_QTY_WEIGHT, self::PRICING_SINGLE], true));
	}

	public function save(bool $selfcheck = true): bool
	{
		if (!$this->exists()
			|| $this->isModified('qty')
			|| $this->isModified('price')
			|| $this->isModified('weight')) {
			$this->recaculateTotal();
		}

		return parent::save($selfcheck);
	}

	public function product(): ?Product
	{
		if (null === $this->_product && null !== $this->product) {
			$this->_product = Products::get($this->product);
		}

		return $this->_product;
	}

	public function recaculateTotal(): void
	{
		$total = $this->qty * $this->price;

		if ($this->pricing === self::PRICING_QTY_WEIGHT) {
			// Cents * grams = Centsgrams / 1000 = cents/kg
			$total = intval(($total * $this->weight) / 1000);
		}

		$this->set('total', $total);
	}

	public function canChangeQty(): bool
	{
		return $this->pricing !== self::PRICING_SINGLE;
	}
}
