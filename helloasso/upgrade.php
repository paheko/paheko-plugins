<?php

namespace Paheko;

$db = DB::getInstance();

$old_version = $plugin->oldVersion();

if (version_compare($old_version, '0.3.3', '<')) {
	$db->import(__DIR__ . '/uninstall.sql');
	$db->import(__DIR__ . '/schema.sql');
	$plugin->setConfig('last_sync', null);
}

if (version_compare($old_version, '1.0.1', '<')) {
	$db->exec('DROP INDEX plugin_helloasso_forms_key;');
	$db->exec('CREATE UNIQUE INDEX IF NOT EXISTS plugin_helloasso_forms_key ON plugin_helloasso_forms(org_slug, type, slug);');
}

if (version_compare($old_version, '1.0.2', '<')) {
	$db->exec('CREATE TABLE IF NOT EXISTS plugin_helloasso_payments_items (
		id_payment INTEGER NOT NULL REFERENCES plugin_helloasso_payments(id) ON DELETE CASCADE,
		id_item INTEGER NOT NULL REFERENCES plugin_helloasso_items(id) ON DELETE CASCADE,
		share_amount INTEGER NULL
	);

	CREATE UNIQUE INDEX IF NOT EXISTS plugin_helloasso_payments_items_unique ON plugin_helloasso_payments_items (id_payment, id_item);');

	$sql = 'SELECT id, raw_data FROM plugin_helloasso_payments;';

	foreach ($db->iterate($sql) as $payment) {
		$data = json_decode($payment->raw_data);

		foreach ($data->items as $item) {
			$db->preparedQuery('REPLACE INTO plugin_helloasso_payments_items (id_payment, id_item, share_amount) VALUES (?, ?, ?);', $payment->id, $item->id, $item->shareAmount);
		}
	}
}
