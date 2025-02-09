<?php

namespace Paheko\Plugin\PIM;
use Paheko\Users\Session;

require __DIR__ . '/_inc.php';

$events = new Events(Session::getUserId());

$y = intval($_GET['y'] ?? 0) ?: date('Y');
$m = intval($_GET['m'] ?? 0) ?: date('m');

$date = \DateTime::createFromFormat('!Y-m-d', $y . '-' . $m . '-01');

$prev = clone $date;
$next = clone $date;
$prev->modify('-1 month');
$next->modify('+1 month');

$prev_year = $date->format('Y') - 1;
$next_year = $date->format('Y') + 1;
$month = $date->format('m');

$calendar = $events->getCalendar($y, $m);

$tpl->assign(compact('calendar', 'date', 'prev', 'next', 'prev_year', 'next_year', 'month'));

$tpl->display(__DIR__ . '/../templates/index.tpl');
