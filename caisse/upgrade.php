<?php

namespace Paheko;

use Paheko\Plugin\Caisse\POS;
use Paheko\Users\DynamicFields;

$db = DB::getInstance();

$old_version = $plugin->oldVersion();

if (version_compare($old_version, '0.2.0', '<')) {
	$db->toggleForeignKeys(false);
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.2.0.sql')));
	$db->toggleForeignKeys(true);
}

if (version_compare($old_version, '0.3.0', '<')) {
	$db->toggleForeignKeys(false);
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.3.0.sql')));
	$db->toggleForeignKeys(true);
}

if (version_compare($old_version, '0.3.1', '<')) {
	$db->toggleForeignKeys(false);
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.3.1.sql')));
	$db->toggleForeignKeys(true);
}

if (version_compare($old_version, '0.3.3', '<')) {
	$db->toggleForeignKeys(false);
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.3.3.sql')));
	$db->toggleForeignKeys(true);
}

if (version_compare($old_version, '0.3.4', '<')) {
	$db->toggleForeignKeys(false);
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.3.4.sql')));
	$db->toggleForeignKeys(true);
}

if (version_compare($old_version, '0.4.1', '<')) {
	$db->toggleForeignKeys(false);
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.4.1.sql')));
	$db->toggleForeignKeys(true);
}

if (version_compare($old_version, '0.5.0', '<')) {
	$db->toggleForeignKeys(false);
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.5.0.sql')));
	$db->toggleForeignKeys(true);
}

if (version_compare($old_version, '0.5.1', '<')) {
	$db->toggleForeignKeys(false);
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.5.1.sql')));
	$db->toggleForeignKeys(true);
}

if (version_compare($old_version, '0.5.3', '<')) {
	$db->toggleForeignKeys(false);
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.5.3.sql')));
	$db->toggleForeignKeys(true);
}

if (version_compare($old_version, '0.5.4', '<')) {
	$db->toggleForeignKeys(false);
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.5.4.sql')));
	$db->toggleForeignKeys(true);
}

if (version_compare($old_version, '0.6.3', '<')) {
	$db->beginSchemaUpdate();
	$identity = DynamicFields::getNameFieldsSQL();
	$sql = str_replace('@__NAME', $identity, POS::sql(file_get_contents(__DIR__ . '/update_0.6.3.sql')));
	$db->exec($sql);
	$db->commitSchemaUpdate();
}

if (version_compare($old_version, '0.7.0', '<')) {
	$db->beginSchemaUpdate();
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.7.0.sql')));
	$db->commitSchemaUpdate();
}

if (version_compare($old_version, '0.7.1', '<')) {
	$db->beginSchemaUpdate();
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.7.1.sql')));
	$db->commitSchemaUpdate();
}

if (version_compare($old_version, '0.8.0', '<')) {
	$db->beginSchemaUpdate();
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.8.0.sql')));
	$db->commitSchemaUpdate();
}

if (version_compare($old_version, '0.8.2', '<')) {
	$db->beginSchemaUpdate();
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.8.2.sql')));
	$db->commitSchemaUpdate();
}

if (version_compare($old_version, '0.8.3', '<')) {
	$db->beginSchemaUpdate();
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.8.3.sql')));
	try {
		// Add column that was missing in schema.sql
		$db->exec(POS::sql('ALTER TABLE @PREFIX_tabs_items ADD COLUMN total INTEGER NOT NULL DEFAULT 0;'));
	}
	catch (\Exception $e) {
		// Ignore error
	}
	$db->commitSchemaUpdate();
}

if (version_compare($old_version, '0.8.4', '<')) {
	$db->beginSchemaUpdate();
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.8.4.sql')));
	$db->commitSchemaUpdate();
}

if (version_compare($old_version, '0.8.5', '<')) {
	$db->beginSchemaUpdate();
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.8.5.sql')));
	$db->commitSchemaUpdate();
}

if (version_compare($old_version, '0.8.6', '<')) {
	$db->beginSchemaUpdate();
	$db->toggleForeignKeys(false);
	$db->exec(POS::sql(file_get_contents(__DIR__ . '/update_0.8.6.sql')));
	$db->commitSchemaUpdate();
	$db->toggleForeignKeys(true);
}
