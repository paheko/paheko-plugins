<?php

namespace Paheko;

$db = DB::getInstance();
$db->import(__DIR__ . '/schema.sql');
