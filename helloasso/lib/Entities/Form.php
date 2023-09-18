<?php

namespace Paheko\Plugin\HelloAsso\Entities;

use Paheko\Entity;
use Paheko\Plugin\HelloAsso\ChargeableInterface;
use KD2\DB\EntityManager;

class Form extends Entity implements ChargeableInterface
{
	const TABLE = 'plugin_helloasso_forms';

	const CHECKOUT_SLUG = 'default';

	protected int		$id;
	protected ?int		$id_chargeable = null;
	protected string	$org_slug;
	protected string	$org_name;
	protected string	$label;
	protected string	$state;

	protected string	$type;
	protected string	$slug;
	protected int		$need_config;

	protected ?array	$_customFields = null;

	const TYPES = [
		'CrowdFunding' => 'Crowdfunding',
		'Membership'   => 'Adhésion',
		'Event'        => 'Billetteries',
		'Donation'     => 'Dons',
		'PaymentForm'  => 'Ventes',
		'Checkout'     => 'Encaissement',
		'Shop'         => 'Boutique',
	];

	const STATES = [
		'Draft'    => 'brouillon',
		'Public'   => 'public',
		'Private'  => 'privé',
		'Disabled' => 'désactivé',
	];

	public function getChargeableId(): ?int
	{
		return $this->id_chargeable;
	}

	public function setChargeableId(int $id): void
	{
		$this->set('id_chargeable', $id);
	}

	public function getType()
	{
		return null;
	}

	public function getItemId(): ?int
	{
		return null;
	}

	public function getLabel(): string
	{
		return $this->label;
	}

	public function getAmount(): ?int
	{
		return null;
	}

	public function getPriceType(): ?int
	{
		return null;
	}

	public function customFields(): array
	{
		if (null === $this->_customFields) {
			$this->_customFields = EntityManager::getInstance(CustomField::class)->all('SELECT * FROM @TABLE WHERE id_form = :id_form;', (int)$this->id);
		}
		return $this->_customFields;
	}

	public function getCustomFields(): ?\stdClass
	{
		return null;
	}

	public function getReference(): string
	{
		return $this->org_slug;
	}

	public function setUserId(?int $id): void {}
	public function getUserId(): ?int { return null; }

	public function createCustomField(string $name, ?int $id_dynamic_field = null): CustomField
	{
		if ($id_dynamic_field && !EntityManager::findOneById(DynamicField::class, $id_dynamic_field)) {
			throw new \InvalidArgumentException(sprintf('Dynamic field #%d does not exist!', $id_dynamic_field));
		}

		$field = new CustomField();
		$field->set('id_form', (int)$this->id);
		$field->set('id_dynamic_field', $id_dynamic_field);
		$field->set('name', $name);
		$field->save();

		return $field;
	}

	public function selfCheck(): void
	{
		parent::selfCheck();
		if (!array_key_exists($this->type, self::TYPES)) {
			throw new \UnexpectedValueException(sprintf('Wrong form (ID: #%d) type: %s. Possible values are: %s.', $this->id ?? null, $this->type, implode(', ', array_keys(self::TYPES))));
		}
		if (!array_key_exists($this->state, self::STATES)) {
			throw new \UnexpectedValueException(sprintf('Wrong form (ID: #%d) status: %s. Possible values are: %s.', $this->id ?? null, $this->state, implode(', ', array_keys(self::STATES))));
		}
	}
}
