<?php

namespace Paheko\Plugin\Invoice;
use Paheko\Plugin\Invoice\Entities\Client;

use Paheko\DB;

$db = DB::getInstance();

$plugin->setConfig((object) [
	'vat_exemption'        => Invoices::DEFAULT_VAT_EXEMPTION,
	'first_invoice_number' => 1,
	'first_quote_number'   => 1,
]);

$db->import(__DIR__ . '/schema.sql');

// Import clients from old plugin
if ($db->hasTable('plugin_facturation_clients')) {
	foreach ($db->iterate('SELECT * FROM plugin_facturation_clients;') AS $row) {
		if (!isset($row->nom, $row->adresse, $row->code_postal, $row->ville, $row->telephone, $row->email)) {
			continue;
		}

		$client = new Client;
		$client->set('id', $row->id);
		$client->import([
			'name'            => $row->nom,
			'address'         => $row->adresse,
			'post_code'       => $row->code_postal,
			'city'            => $row->ville,
			'phone'           => $row->telephone,
			'email'           => $row->email,
			'notes'           => $row->note ?? '',
			'business_number' => isset($row->siret) ? substr($row->siret, 0, 9) : null,
			'country'         => 'FR',
		]);
		$client->set('created', new \DateTime);
		$client->save();
	}

	// Cannot import invoices as the format is too different
}
