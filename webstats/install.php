<?php

use Garradin\Plugin\Webstats\Stats;

$db->import(__DIR__ . '/schema.sql');

$plugin->registerSignal('web.request', 'Garradin\Plugin\Webstats\Stats::webRequest');
