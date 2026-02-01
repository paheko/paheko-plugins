<?php

namespace Paheko\Plugin\HelloAsso\Entities;

use Paheko\DB;
use Paheko\Entity;

class TierOption extends Entity
{
	const TABLE = 'plugin_helloasso_forms_tiers_options';

	protected int $id;
	protected int $id_form;

	protected ?string $label;
	protected ?int $amount;

	protected ?string $account_code = null;

	public function linkTo(int $id_tier): void
	{
		$db = DB::getInstance();
		$db->preparedQuery('REPLACE INTO plugin_helloasso_forms_tiers_options_links (id_tier, id_tier_option) VALUES (?, ?);', $id_tier, $this->id());
	}
}
