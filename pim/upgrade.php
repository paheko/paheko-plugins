<?php

namespace Paheko;

use Paheko\Plugin\Caisse\POS;
use Paheko\Users\DynamicFields;

$db = DB::getInstance();

$old_version = $plugin->oldVersion();

if (version_compare($old_version, '0.1.2', '<')) {
	$db->toggleForeignKeys(false);
	$db->exec('
CREATE TABLE IF NOT EXISTS plugin_pim_credentials (
	id_user INTEGER NOT NULL PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
	password TEXT NOT NULL
);');
	$db->toggleForeignKeys(true);
}
