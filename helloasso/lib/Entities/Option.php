<?php

namespace Paheko\Plugin\HelloAsso\Entities;

use Paheko\Plugin\HelloAsso\Forms;

use Paheko\DB;
use Paheko\Entity;

class Option extends Entity
{
	const TABLE = 'plugin_helloasso_forms_options';

	protected int $id;
	protected int $id_form;

	protected ?string $label;
	protected ?int $amount;

	protected ?string $account_code = null;

	protected ?Form $_form = null;

	public function linkTo(int $id_tier): void
	{
		$db = DB::getInstance();
		$db->preparedQuery('REPLACE INTO plugin_helloasso_forms_tiers_options_links (id_tier, id_option) VALUES (?, ?);', $id_tier, $this->id());
	}

	public function getAccountCode(): ?string
	{
		if ($this->account_code) {
			return $this->account_code;
		}

		return $this->form()->payment_account_code;
	}

	public function form(): Form
	{
		$this->_form ??= Forms::get($this->id_form);
		return $this->_form;
	}

}
