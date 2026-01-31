<?php

namespace Paheko\Plugin\Caisse\Entities;

use Paheko\Plugin\Caisse\POS;
use Paheko\Entity;

class SessionBalance extends Entity
{
	const TABLE = POS::TABLES_PREFIX . 'sessions_balances';

	protected ?int $id;
	protected int $id_session;
	protected ?int $id_method = null;
	protected int $open_amount;
	protected ?int $close_amount = null;
	protected ?int $error_amount = null;
}
