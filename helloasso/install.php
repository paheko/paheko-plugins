<?php

namespace Paheko;

$db = DB::getInstance();

// Création table
$db->import(__DIR__ . '/schema.sql');
