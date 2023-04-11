<?php

use Garradin\Plugin\Webstats\Stats;

$plugin->unregisterSignal('http.request.skeleton.before');
$plugin->unregisterSignal('http.request.skeleton.after');

$plugin->registerSignal('web.request', 'Garradin\Plugin\Webstats\Stats::webRequest');
