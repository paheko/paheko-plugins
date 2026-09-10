<?php

namespace Paheko;

$db = DB::getInstance();
$db->exec('
DROP TABLE IF EXISTS plugin_shortcuts_shortcuts;
');