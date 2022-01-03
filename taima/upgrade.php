<?php

namespace Garradin;

$old_version = $plugin->getInfos('version');
$db = DB::getInstance();

// Fix year for dates that are in the last week of previous year
// eg. 2022-01-01 is in week 52 of year 2021
if (version_compare($old_version, '0.4', '<')) {
	$dates = ['2022-01-01', '2022-01-02', '2021-01-01', '2021-01-02', '2021-01-03'];
	$db->exec(sprintf('UPDATE plugin_taima_entries SET year = year - 1 WHERE %s;', $db->where('date', $dates)));

	$db->exec('ALTER TABLE plugin_taima_tasks ADD COLUMN value INTEGER NULL;');
	$db->exec('ALTER TABLE plugin_taima_tasks ADD COLUMN account TEXT NULL;');
}
