<?php

namespace Paheko;

$db = DB::getInstance();

preg_match_all('/CREATE (?:VIRTUAL )?TABLE IF NOT EXISTS (plugin_discuss_\w+)/', file_get_contents(__DIR__ . '/schema.sql'), $match, PREG_PATTERN_ORDER);
$tables = array_reverse($match[1]);

foreach ($tables as $table) {
	$db->exec(sprintf('DROP TABLE IF EXISTS %s;', $table));
}

