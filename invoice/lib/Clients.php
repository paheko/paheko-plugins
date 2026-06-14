<?php

namespace Paheko\Plugin\Invoice;

use Paheko\Config;
use Paheko\Plugin\Invoice\Entities\Client;

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
}