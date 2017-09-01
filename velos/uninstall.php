<?php

namespace Garradin;

$db = DB::getInstance();

// Suppression table
$db->exec('DROP TABLE IF EXISTS plugin_stock_velos;');