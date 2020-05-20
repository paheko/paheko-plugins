<?php

namespace Garradin\Plugin\Caisse;

use Garradin\DB;

class POS
{
	const TABLES_PREFIX = 'plugin_pos_';

	static public function sql(string $query): string
	{
		return str_replace('@PREFIX_', self::TABLES_PREFIX, $query);
	}

	static public function tbl(string $table): string
	{
		return self::TABLES_PREFIX . $table;
	}
}