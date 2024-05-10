<?php

namespace Paheko;

use Paheko\Plugin\Stock_Velos\Velos;

$plugin->unregisterSignal('usertemplate.init');

$old_version = $plugin->oldVersion();
$db = DB::getInstance();

if (version_compare($old_version, '4.2.0', '<')) {
	$db->exec('ALTER TABLE plugin_stock_velos ADD COLUMN poids INTEGER NULL; CREATE INDEX IF NOT EXISTS prv_poids ON plugin_stock_velos (poids);');
}
