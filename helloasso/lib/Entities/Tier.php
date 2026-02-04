<?php

namespace Paheko\Plugin\HelloAsso\Entities;

use Paheko\Plugin\HelloAsso\HelloAsso;
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

	protected int $create_user = HelloAsso::NO_USER_ACTION;

	const TYPES = Item::TYPES;
	const TYPES_ACCOUNTS = Item::TYPES_ACCOUNTS;
	const TYPES_COLORS = Item::TYPES_COLORS;

	protected Form $_form;

	public function getTypeAccount(): string
	{
		return self::TYPES_ACCOUNTS[$this->type];
	}

	public function getTypeLabel(): string
	{
		return self::TYPES[$this->type];
	}

	public function getTypeColor(): string
	{
		return self::TYPES_COLORS[$this->type];
	}

	public function getAccountCode(): ?string
	{
		if ($this->account_code) {
			return $this->account_code;
		}
		elseif ($this->form()->payment_account_code) {
			return $this->form()->payment_account_code;
		}
		elseif ($this->getTypeAccount() === 'donation') {
			return HelloAsso::getInstance()->getConfig()->donation_account_code ?? null;
		}

		return null;
	}

	public function form(): Form
	{
		$this->_form ??= Forms::get($this->id_form);
		return $this->_form;
	}

	public function importForm(?array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['account_code']) && is_array($source['account_code'])) {
			$source['account_code'] = key($source['account_code']);
		}

		parent::importForm($source);
	}
}
