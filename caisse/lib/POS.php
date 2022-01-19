<?php

namespace Garradin\Plugin\Caisse;

use Garradin\DB;

use KD2\Graphics\SVG\Bar;
use KD2\Graphics\SVG\Bar_Data_Set;

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

	static public function barGraph(?string $title, array $data): string
	{
		$bar = new Bar(1000, 400);
		$bar->setTitle($title);
		$current_group = null;
		$set = null;
		$sum = 0;

		$color = function (string $str): string {
			return sprintf('#%s', substr(md5($str), 0, 6));
		};

		foreach ($data as $group_label => $group) {
			$set = new Bar_Data_Set($group_label);
			$sum = 0;

			foreach ($group as $label => $value) {
				$set->add($value, $label, $color($label));
				$sum += $value;
			}

			$label = 'Total';
			$set->add($sum, $label, $color($label));
			$bar->add($set);
		}

		return $bar->output();
	}
}