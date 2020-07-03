<?php

namespace Garradin\Plugin\Caisse;

use Garradin\DB;

class Product
{
	static public function listByCategory(): array
	{
		$db = DB::getInstance();
		$categories = $db->getAssoc(POS::sql('SELECT id, name FROM @PREFIX_categories ORDER BY name;'));

		// Don't select products that don't have any payment method linked: you wouldn't be able to pay for them
		$products = $db->get(POS::sql('SELECT * FROM @PREFIX_products p
			INNER JOIN @PREFIX_products_methods m ON m.product = p.id
			GROUP BY p.id ORDER BY category, transliterate_to_ascii(name) COLLATE NOCASE;'));

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
