<?php

$plugin->setConfig((object) [
	'vat_exemption' => Invoices::DEFAULT_VAT_EXEMPTION,
]);

// Import clients from old plugin
if ($db->hasTable('plugin_facturation_clients')) {
	foreach ($db->iterate('SELECT * FROM plugin_facturation_clients;') AS $row) {
		$client = new Client;
		$client->set('id', $row->id);
		$client->import([
			'name' => $row->nom,
			'address' => $row->adresse,
			'post_code' => $row->code_postal,
			'city' => $row->ville,
			'phone' => $row->telephone,
			'email' => $row->email,
			'notes' => $row->note,
			'business_number' => $row->siret ? substr($row->siret, 0, 9) : null,
			'country' => 'FR',
		]);
		$client->save();
	}

	// Cannot import invoices as the format is too different
}
