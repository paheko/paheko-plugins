<?php

namespace Paheko;

$db = DB::getInstance();

$db->exec(file_get_contents(__DIR__ . '/schema.sql'));
