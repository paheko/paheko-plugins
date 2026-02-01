<?php

namespace Paheko\Plugin\HelloAsso\Entities;

use Paheko\Entity;

class TierOption extends Entity
{
	const TABLE = 'plugin_helloasso_forms_tiers_options';

	protected int $id;
	protected int $id_form;
	protected int $id_tier;

	protected ?string $label;
	protected ?int $amount;

	protected ?string $account_code = null;
}
