<?php

namespace Garradin;

$db = DB::getInstance();

$db->exec('DROP TABLE IF EXISTS plugin_reservations_categories;');
$db->exec('DROP TABLE IF EXISTS plugin_reservations_creneaux;');
$db->exec('DROP TABLE IF EXISTS plugin_reservations_personnes;');
