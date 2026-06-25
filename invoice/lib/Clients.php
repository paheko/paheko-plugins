<?php

namespace Paheko\Plugin\Invoice;

use Paheko\Config;
use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Plugin\Invoice\Entities\Client;

use KD2\DB\EntityManager as EM;

class Clients
{
	static public function exportOrgForInvoice(): array
	{
		$config = Config::getInstance();

		$person = (object) [
			'name'            => $config->org_name,
			'country'         => $config->org_country,
			'address'         => $config->org_address,
			'post_code'       => $config->org_post_code,
			'city'            => $config->org_city,
			'email'           => $config->org_email,
			'phone'           => $config->org_phone,
			'vat_number'      => $config->org_vat_number,
			'business_number' => $config->org_business_number,
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