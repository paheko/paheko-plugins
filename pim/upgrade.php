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

if (version_compare($old_version, '0.1.3', '<')) {
	$sql = sprintf('INSERT INTO users_app_passwords (id_user, id_plugin, name, password)
		SELECT id_user, %d, \'Agenda et contacts (accès CardDAV/CalDAV)\', password FROM plugin_pim_credentials;', $plugin->id);
	$sql .= "\nDROP TABLE plugin_pim_credentials;";
	$db->exec($sql);
}
