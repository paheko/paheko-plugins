<?php

namespace Paheko\Plugin\Taima;

use Paheko\Plugin\Taima\Tracking;
use Paheko\Utils;
use Paheko\Users\Session;

use KD2\DB\Date;

use function Paheko\{f, qg};

$user_id = Session::getUserId();

function taima_url($day = null)
{
	return Utils::plugin_url($day ? ['query' => 'day=' . (Utils::parseDateTime($day))->format('Y-m-d')] : []);
}

$day = $today = new Date;
$day->setTime(0, 0, 0);

if (qg('day')) {
	$day = Date::createFromFormat('!Y-m-d', qg('day'));
}

$tpl->register_modifier('taima_date', function ($date, string $format) {
	$date = Utils::parseDateTime($date);
	return \IntlDateFormatter::formatObject($date, $format, 'fr_FR');
});

$tpl->register_modifier('taima_minutes', [Tracking::class, 'formatMinutes']);
$tpl->register_modifier('taima_url', __NAMESPACE__ . '\taima_url');
$tpl->assign('plugin_css', ['style.css']);

$tpl->assign('legal_hours', Tracking::getWorkingHours());
