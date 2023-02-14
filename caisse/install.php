<?php

namespace Garradin;

use Garradin\Plugin\Caisse\POS;

$db = DB::getInstance();

$db->exec(POS::sql(file_get_contents(__DIR__ . '/schema.sql')));
$db->exec(POS::sql(file_get_contents(__DIR__ . '/data.sql')));
