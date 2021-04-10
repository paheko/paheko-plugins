<?php

namespace Garradin\Plugin\Caisse\Entities;

use Garradin\Plugin\Caisse\POS;
use Garradin\Entity;


class Method extends Entity
{
	const TABLE = POS::TABLES_PREFIX . 'methods';

	protected int $id;
	protected string $name;
	protected bool $is_cash;
	protected ?int $min;
	protected ?int $max;
	protected ?string $account;
}