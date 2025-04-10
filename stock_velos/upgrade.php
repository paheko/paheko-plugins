<?php

namespace Paheko;

use Paheko\Plugin\Stock_Velos\Velos;

$plugin->unregisterSignal('usertemplate.init');

$old_version = $plugin->oldVersion();
$db = DB::getInstance();

if (version_compare($old_version, '4.2.0', '<')) {
	$db->exec('ALTER TABLE plugin_stock_velos ADD COLUMN poids INTEGER NULL; CREATE INDEX IF NOT EXISTS prv_poids ON plugin_stock_velos (poids);');
}

if (version_compare($old_version, '4.3.0', '<')) {
	$defaults = [
		'type' => $plugin->getConfig('types'),
		'taille' => $plugin->getConfig('tailles'),
		'source' => $plugin->getConfig('sources'),
		'genre' => $plugin->getConfig('genres'),
		'raison_sortie' => $plugin->getConfig('raisons_sortie'),
		'source_details' => $plugin->getConfig('sources_details'),
	];

	$defaults = array_filter($defaults);
	$plugin->setConfigProperty('defaults', $defaults);

	$plugin->setConfigProperty('types', null);
	$plugin->setConfigProperty('tailles', null);
	$plugin->setConfigProperty('sources', null);
	$plugin->setConfigProperty('genres', null);
	$plugin->setConfigProperty('raisons_sortie', null);
	$plugin->setConfigProperty('sources_details', null);
}

if (version_compare($old_version, '4.3.1', '<')) {
	$db->exec('
		CREATE INDEX IF NOT EXISTS prv_poids2 ON plugin_stock_velos(raison_sortie, poids);
		CREATE INDEX IF NOT EXISTS prv_poids3 ON plugin_stock_velos(source, poids);
	');
}

if (version_compare($old_version, '4.3.2', '<')) {
	$db->exec('
		ALTER TABLE plugin_stock_velos ADD COLUMN taille TEXT NULL;
	');
}
