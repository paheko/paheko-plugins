<?php

namespace Paheko;

use Paheko\Plugin\Caisse\POS;
use Paheko\Users\DynamicFields;

$db = DB::getInstance();

$old_version = $plugin->oldVersion();

if (version_compare($old_version, '0.1.1', '<')) {
	$db->exec('ALTER TABLE plugin_invoice_clients ADD COLUMN self_billing INTEGER NOT NULL DEFAULT 0;');
}

if (version_compare($old_version, '0.1.2', '<')) {
	$db->exec('ALTER TABLE plugin_invoice_clients ADD COLUMN electronic_address TEXT NULL;');
}

if (version_compare($old_version, '0.1.3', '<')) {
	$db->exec('ALTER TABLE plugin_invoice_clients ADD COLUMN id_user INTEGER NULL REFERENCES users (id) ON DELETE SET NULL;');
}

if (version_compare($old_version, '0.1.4', '<')) {
	$db->exec('ALTER TABLE plugin_invoice_clients ADD COLUMN e_invoicing INTEGER NOT NULL DEFAULT 0;
		UPDATE plugin_invoice_clients SET e_invoicing = 1 WHERE business_number IS NOT NULL AND country = \'FR\';');
}

if (version_compare($old_version, '0.1.5', '<')
	&& $db->firstColumn('SELECT 1 FROM sqlite_master WHERE name = \'plugin_invoice_invoices\' AND sql LIKE \'%contract_reference%\';')) {
	$db->exec('ALTER TABLE plugin_invoice_invoices RENAME COLUMN contract_reference TO purchase_order_reference');

	// Rename contract_reference field
	foreach ($db->iterate('SELECT id, content FROM plugin_invoice_invoices WHERE content IS NOT NULL;') as $row) {
		$content = json_decode($row->content);
		$content->purchase_order_reference = $content->contract_reference;
		unset($content->contract_reference);
		$content = json_encode($content);

		$db->update('plugin_invoice_invoices', compact('content'), 'id = ' . (int) $row->id);
	}
}
