<?php

namespace Paheko;

use Paheko\Plugin\Taima\Tracking;

$db = DB::getInstance();
$db->import(__DIR__ . '/schema.sql');
