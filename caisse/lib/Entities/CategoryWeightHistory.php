<?php

namespace Paheko\Plugin\Caisse\Entities;

use Paheko\Entity;
use Paheko\Plugin\Caisse\POS;
use KD2\DB\EntityManager as EM;

class CategoryWeightHistory extends Entity
{
	const TABLE = POS::TABLES_PREFIX . 'categories_weight_history';

	protected ?int $id;
	protected int $category;
	protected int $weight_change;
	protected \DateTime $date;
	protected ?int $item;
	protected ?int $event;

	public function importForm(array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['change'])) {
			$source['change'] = Utils::weightToInteger($source['change']);
		}

		parent::importForm($source);
	}
}
