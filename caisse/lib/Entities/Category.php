<?php

namespace Paheko\Plugin\Caisse\Entities;

use Paheko\Plugin\Caisse\POS;
use Paheko\Entity;
use Paheko\Utils;
use KD2\DB\EntityManager as EM;

class Category extends Entity
{
	const TABLE = POS::TABLES_PREFIX . 'categories';

	protected ?int $id;
	protected string $name;
	protected ?string $account = null;

	public function selfCheck(): void
	{
		$this->assert(isset($this->name) && trim($this->name) !== '', 'Le nom ne peut rester vide.');
	}

	public function importForm(?array $source = null): void
	{
		$source ??= $_POST;

		if (isset($source['account']) && is_array($source['account'])) {
			$source['account'] = key($source['account']);
		}

		parent::importForm($source);
	}
}