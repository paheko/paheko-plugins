<?php

namespace Paheko\Plugin\Taima;

use Paheko\Entity;
use Paheko\Plugin\Taima\Tracking;

use function Paheko\{f, qg};

require_once __DIR__ . '/_inc.php';

$session->requireAccess($session::SECTION_USERS, $session::ACCESS_ADMIN);

$per_user = !empty(qg('per_user'));
$grouping = qg('g') ?? 'week';
$start = Entity::filterUserDateValue(qg('start') ?: null);
$end = Entity::filterUserDateValue(qg('end') ?: null);

$per_week = Tracking::listPerInterval($grouping, $per_user, $start, $end);

$filter_dates = $start && $end ? sprintf('&start=%s&end=%s', $start->format('d/m/Y'), $end->format('d/m/Y')) : null;
$start ??= new \DateTime('first day of this year');
$end ??= new \DateTime('last day of this year');

$tpl->assign(compact('per_week', 'per_user', 'grouping', 'start', 'end', 'filter_dates'));

$tpl->display(__DIR__ . '/../templates/stats.tpl');
