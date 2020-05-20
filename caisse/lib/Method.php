<?php

namespace Garradin\Plugin\Caisse;

use Garradin\DB;

class Method
{
	static public function getList(): array
	{
		return DB::getInstance()->getAssoc(POS::sql('SELECT id, name FROM @PREFIX_methods ORDER BY name;'));
	}
}
