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
