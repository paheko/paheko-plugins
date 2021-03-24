<?php

namespace Garradin;

use Garradin\Plugin\Caisse\POS;

$db = DB::getInstance();

$old_version = $plugin->getInfos('version');

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
