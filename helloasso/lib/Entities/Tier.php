<?php

namespace Paheko\Plugin\HelloAsso\Entities;

use Paheko\Plugin\HelloAsso\Forms;

use Paheko\Entity;

use KD2\DB\EntityManager as EM;

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

	protected int $create_user = self::NO_USER_ACTION;

	const TYPES = Item::TYPES;
	const TYPES_COLORS = Item::TYPES_COLORS;

	const NO_USER_ACTION = 0;
	const CREATE_UPDATE_USER = 1;
	const UPDATE_USER = 2;

	protected Form $_form;

	public function getTypeLabel(): string
	{
		return self::TYPES[$this->type];
	}

	public function getTypeColor(): string
	{
		return self::TYPES_COLORS[$this->type];
	}

	public function form(): Form
	{
		$this->_form ??= Forms::get($this->id_form);
		return $this->_form;
	}

	public function listOptions(): array
	{
		return EM::getInstance(Option::class)->all('SELECT * FROM @TABLE WHERE id_tier = ? ORDER BY label COLLATE U_NOCASE, amount;', $this->id());
	}
}
