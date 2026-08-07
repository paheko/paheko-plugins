<?php

namespace Paheko\Plugin\Invoice;

use Paheko\Config;
use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Plugin\Invoice\Entities\Client;

use KD2\DB\EntityManager as EM;

use stdClass;

class Clients
{
	static public function exportOrgForInvoice(): stdClass
	{
		$config = Config::getInstance();

		$number = $config->org_business_number;

		if ($config->country === 'FR') {
			// SIREN is mandatory in Factur-X
			// BR-FR-10/BT-30 : Le SIREN du vendeur (ram:ID) est obligatoire et doit être composé exactement de 9 chiffres
			$number = substr($config->org_business_number, 0, 9);
		}

		$person = (object) [
			'name'            => $config->org_name,
			'country'         => $config->country,
			'address'         => $config->org_address,
			'post_code'       => $config->org_post_code,
			'city'            => $config->org_city,
			'email'           => $config->org_email,
			'phone'           => $config->org_phone,
			'vat_number'      => $config->org_vat_number, // FIXME
			'business_number' => $number,
		];

		return Client::exportPersonForInvoice($person);
	}

	static public function get(int $id): ?Client
	{
		return EM::findOneById(Client::class, $id);
	}

	static public function countActiveClients(): int
	{
		return DB::getInstance()->count(Client::TABLE, 'archived = 0');
	}

	static public function getList(bool $archived = false, ?string $search = null): DynamicList
	{
		$columns = [
			'id' => [],
			'name' => [
				'label' => 'Nom',
			],
		];

		$params = [];
		$conditions = sprintf('archived = %d', $archived);

		if ($search) {
			$conditions .= ' AND name LIKE ? COLLATE U_NOCASE';
			$params[] = $search;
		}

		$list = new DynamicList($columns, Client::TABLE, $conditions);
		$list->orderBy('name', false);
		$list->setParameters($params);

		return $list;
	}
}