<?php

namespace Garradin\Plugin\Caisse;

use Garradin\DB;

class Product
{
	static public function listByCategory(): array
	{
		$db = DB::getInstance();
		$categories = $db->getAssoc(POS::sql('SELECT id, name FROM @PREFIX_categories ORDER BY name;'));
		$products = $db->get(POS::sql('SELECT * FROM @PREFIX_products ORDER BY category, name;'));

		$list = [];

		foreach ($products as $product) {
			$cat = $categories[$product->category];

			if (!array_key_exists($cat, $list)) {
				$list[$cat] = [];
			}

			$list[$cat][] = $product;
		}

		return $list;
	}
}
