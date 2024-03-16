<?php

namespace Paheko\Plugin\Taima;

use Paheko\Entity;
use Paheko\Plugin\Taima\Tracking;

use function Paheko\{f, qg};

require_once __DIR__ . '/_inc.php';

$session->requireAccess($session::SECTION_USERS, $session::ACCESS_ADMIN);

$filters = [];

if ($start = qg('start')) {
	$filters['start'] = $start;
}

if ($end = qg('end')) {
	$filters['end'] = $end;
}

$period = qg('p') ?? 'week';
$group = qg('g') ?? 'task';

$list = Tracking::listPerInterval($period, $group, $filters);
$list->loadFromQueryString();

$filters_uri = http_build_query($filters);
$filters['start'] ??= new \DateTime('first day of this year');
$filters['end'] ??= new \DateTime('last day of this year');

$tpl->assign(compact('period', 'group', 'filters', 'filters_uri', 'list'));

$tpl->display(__DIR__ . '/../templates/stats.tpl');
