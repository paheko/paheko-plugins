<?php

namespace Paheko\Plugin\Taima;

use Paheko\Plugin\Taima\Tracking;
use Paheko\Users\Session;
use Paheko\Utils;

require_once __DIR__ . '/_inc.php';

// If there is no user id
if (!Session::getUserId()) {
	Utils::redirect('./all.php');
}

$user = $session->getUser();

$hours = $_GET['target_hours'] ?? null;
$hours ??= $user->getPreference('taima_week_hours');
$hours ??= 13;
$hours = (int) $hours;

if ($hours !== 13) {
	$user->setPreference('taima_week_hours', $hours);
}


$target = Tracking::getWorkingHours($hours);

$user_id = $user->id;
$year = (int) ($_GET['year'] ?? null);
$years = $months = $weeks = null;

if ($year) {
	$months = Tracking::listUserMonths($user_id, $year);
	$weeks = Tracking::listUserWeeks($user_id, $year);
}
else {
	$years = Tracking::listUserYears($user_id);
}

$tpl->assign(compact('weeks', 'months', 'years', 'target'));

$tpl->display(\Paheko\PLUGIN_ROOT . '/templates/year.tpl');
