<?php

namespace Paheko\Plugin\Taima\Entities;

use Paheko\Accounting\Accounts;

use Paheko\Entity;
use Paheko\Form;
use Paheko\Utils;

class Task extends Entity
{
	const TABLE = 'plugin_taima_tasks';

	protected ?int $id;
	protected string $label;
	protected ?int $value;
	protected ?string $account;
	protected ?int $id_project;

	public function importForm(?array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['value'])) {
			$source['value'] = Utils::moneyToInteger($source['value']) ?: null;
		}

		if (isset($source['account']) && is_array($source['account'])) {
			$source['account'] = Form::getSelectorValue($source['account']);
		}

		return parent::importForm($source);
	}

	public function selfCheck(): void
	{
		$this->assert(strlen(trim($this->label)), 'Le libellé ne peut être laissé vide.');
		$this->assert(isset($this->value, $this->account)
			|| (!isset($this->value) && !isset($this->account)),
			'Il faut spécifier à la fois le compte et la valorisation, ou aucun des deux.');
		$this->assert(!isset($this->id_project) || isset($this->account), 'Le projet ne peut être spécifié sans spécifier un compte de valorisation.');
		parent::selfCheck();
	}
}
