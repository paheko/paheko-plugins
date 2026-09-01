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
