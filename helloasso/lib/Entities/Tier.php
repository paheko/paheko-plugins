<?php

namespace Paheko\Plugin\HelloAsso\Entities;

use Paheko\Entity;

class Tier extends Entity
{
	const TABLE = 'plugin_helloasso_forms_tiers';

	protected int $id;
	protected int $id_form;

	protected ?string $label;
	protected ?int $amount;
	protected string $type;

	protected ?array $custom_fields = null;

	protected ?int $id_fee = null;
	protected ?string $account_code = null;
	protected ?array $fields_map = null;
	protected bool $use_payer_fallback_info = false;

	const TYPES = Item::TYPES;
	const TYPES_COLORS = Item::TYPES_COLORS;

	public function canMatchUsers(): bool
	{
		return !empty($this->fields_map) || $this->use_payer_fallback_info;
	}

	public function getTypeLabel(): string
	{
		return self::TYPES[$this->type];
	}

	public function getTypeColor(): string
	{
		return self::TYPES_COLORS[$this->type];
	}
}
