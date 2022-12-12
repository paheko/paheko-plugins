<?php

use Garradin\Plugin\Test\Test;

$plugin->registerSignal('home.button', [Test::class, 'homeButton']);
$plugin->registerSignal('menu.item', [Test::class, 'menuItem']);
