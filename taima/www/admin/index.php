<?php

namespace Garradin\Plugin\Taima;

use Garradin\Plugin\Taima\Tracking;
use Garradin\Plugin\Taima\Entities\Entry;

use Garradin\Utils;

use function Garradin\{f, qg};

use DateTime;

if ($plugin->needUpgrade()) {
	$plugin->upgrade();
}

$csrf_key = 'plugin_taima_sheet';
$user_id = $session->getUser()->id;

function taima_url(?DateTime $day = null)
{
	return Utils::plugin_url($day ? ['query' => 'day=' . $day->format('Y-m-d')] : []);
}

$day = $today = new DateTime;
$day->setTime(0, 0, 0);

if (qg('day')) {
	$day = DateTime::createFromFormat('!Y-m-d', qg('day'));
}

$tpl->register_modifier('taima_date', function (\DateTime $date, string $format) {
	return \IntlDateFormatter::formatObject($date, $format, 'fr_FR');
});

$tpl->register_modifier('taima_minutes', [Tracking::class, 'formatMinutes']);
$tpl->register_function('taima_url', function (array $params) {
	return Utils::plugin_url(['query' => http_build_query($params)]);
});

$form->runIf('add', function () use ($day, $user_id) {
	$entry = new Entry;
	$entry->setDate($day);
	$entry->user_id = $user_id;
	$entry->importForm();
	$entry->setDuration(f('duration'));
	$entry->save();
}, $csrf_key, taima_url($day));

$form->runIf('edit', function () {
	if (!is_array(f('edit')) || !count(f('edit'))) {
		throw new \LogicException('Not an array');
	}

	$id = key(f('edit'));
	$entry = Tracking::get((int) $id);
	$entry->importForm();
	$entry->setDuration(f('duration'));
	$entry->save();
}, $csrf_key, taima_url($day));

$form->runIf('delete', function () {
	if (!is_array(f('delete')) || !count(f('delete'))) {
		throw new \LogicException('Not an array');
	}

	$id = key(f('delete'));
	$entry = Tracking::get((int) $id);
	$entry->delete();
}, $csrf_key, taima_url($day));

if (qg('start')) {
	$entry = Tracking::get((int) qg('start'));
	$entry->start();
	$entry->save();
	Utils::redirect(taima_url($entry->date));
}
elseif (qg('stop')) {
	$entry = Tracking::get((int) qg('stop'));
	$entry->stop();
	$entry->save();
	Utils::redirect(taima_url($entry->date));
}

$prev_url = taima_url((clone $day)->modify('-1 week'));
$next_url = taima_url((clone $day)->modify('+1 week'));
$today_url = taima_url($today);

$year = (int) $day->format('o');
$week = (int) $day->format('W');

$weekdays = Tracking::getWeekDays($year, $week, $user_id);
$week_total = 0;

// Add URL
array_walk($weekdays, function (&$row) use (&$week_total) {
	$row->url = taima_url($row->day);
	$row->minutes_formatted = Tracking::formatMinutes($row->minutes ?? 0);
	$week_total += $row->minutes;
});

$week_total = Tracking::formatMinutes($week_total);

$entries = Tracking::listUserEntries($day, $user_id);

$tasks = ['' => '--'] + Tracking::listTasks();

$is_today = $day->format('Ymd') == $today->format('Ymd');

$tpl->assign(compact('is_today', 'tasks', 'entries', 'week_total', 'weekdays', 'prev_url', 'next_url', 'today_url', 'day', 'year', 'week', 'csrf_key'));

$tpl->display(__DIR__ . '/../../templates/index.tpl');
