<?php

namespace Garradin;

$plugin->registerSignal('boucle.velos', ['Garradin\Velos_Signaux', 'LoopVelos']);

$db = DB::getInstance();

// Renomme table
if ($db->test('sqlite_master' 'type = \'table\' AND name = \'plugin_rustine_velos\''))
{
	$db->exec('ALTER TABLE plugin_rustine_velos RENAME TO plugin_stock_velos;');
}