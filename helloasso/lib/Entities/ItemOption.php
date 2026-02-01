<?php

namespace Paheko\Plugin\HelloAsso\Entities;

use Paheko\Entity;

class ItemOption extends Entity
{
	const TABLE = 'plugin_helloasso_items_options';

	protected int $id;
	protected int $id_form;
	protected int $id_order;
	protected int $id_item;
	protected int $id_option;

	protected ?string $label;
	protected ?int $amount;
	protected string $raw_data;

	protected array $custom_fields;
}
