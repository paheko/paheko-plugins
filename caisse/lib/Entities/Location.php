<?php

namespace Paheko\Plugin\Caisse\Entities;

use Paheko\Plugin\Caisse\POS;
use Paheko\Entity;
use Paheko\ValidationException;

use KD2\DB\EntityManager;

class Location extends Entity
{
	const TABLE = POS::TABLES_PREFIX . 'locations';

	protected ?int $id;
	protected string $name = '';

	public function selfCheck(): void
	{
		$this->assert(!empty($this->name) && trim($this->name) !== '', 'Le nom ne peut rester vide.');
	}

	public function delete(): bool
	{
		$db = EntityManager::getInstance(static::class)->DB();

		if ($db->test(POS::TABLES_PREFIX . 'sessions', 'id_location = ?', $this->id)) {
			throw new ValidationException('Ce lieu ne peut être supprimé car il est utilisé dans des sessions de caisse.');
		}

		if ($db->test(POS::TABLES_PREFIX . 'methods', 'id_location = ?', $this->id)) {
			throw new ValidationException('Ce lieu ne peut être supprimé car des moyens de paiement y sont liés.');
		}

		return parent::delete();
	}
}
