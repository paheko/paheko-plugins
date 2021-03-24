<?php

namespace Garradin\Plugin\Caisse\Entities;

use Garradin\Plugin\Caisse\POS;
use Garradin\Entity;
use Garradin\Utils;
use KD2\DB\EntityManager as EM;

class Category extends Entity
{
	const TABLE = POS::TABLES_PREFIX . 'categories';

	protected int $id;
	protected string $name = '';
	protected ?string $account = null;
}