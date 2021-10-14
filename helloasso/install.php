<?php

namespace Garradin;

// Création table
$db->import(__DIR__ . '/schema.sql');

$plugin->registerSignal('cron', 'Garradin\Plugin\HelloAsso\HelloAsso::cron');
