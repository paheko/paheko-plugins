<?php

namespace Garradin;

$db->import(__DIR__ . '/schema.sql');

$plugin->registerSignal('home.button', [Tracking::class, 'homeButton']);
