<?php

namespace Garradin\Plugin\Taima;

use Garradin\Plugin\Taima\Tracking;
use Garradin\Plugin\Taima\Entities\Entry;

use Garradin\Utils;

use function Garradin\{f, qg};

use DateTime;


$tpl->register_modifier('taima_minutes', [Tracking::class, 'formatMinutes']);

$per_user = qg('per_user') !== null;
$grouping = qg('g') ?? 'week';
$per_week = Tracking::listPerWeek($grouping, $per_user);

$tpl->assign(compact('per_week', 'per_user', 'grouping'));


$tpl->display(__DIR__ . '/../../templates/stats.tpl');
