<?php

namespace Garradin;

$db->import(__DIR__ . '/schema.sql');

$plugin->registerSignal('http.request', 'Garradin\Plugin\Webstats\Stats::signalBefore');
$plugin->registerSignal('http.request.skeleton.after', 'Garradin\Plugin\Webstats\Stats::signalAfter');
$plugin->registerSignal('http.request.file.after', 'Garradin\Plugin\Webstats\Stats::signalAfter');
