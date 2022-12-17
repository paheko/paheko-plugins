<?php

namespace Garradin;

$db->import(__DIR__ . '/schema.sql');

$plugin->registerSignal('menu.item', [Tracking::class, 'menuItem']);
$plugin->registerSignal('home.button', [Tracking::class, 'homeButton']);
