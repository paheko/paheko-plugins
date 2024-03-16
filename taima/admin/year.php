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

// 1607 hours = numbers of hours worked in a year for a 35 hour week,
// counting holidays
// 35*52 = what you would do as simple math
$legal_work_ratio = 1607/(35*52);

$legal_hours = 35;
$legal_week = $legal_hours * $legal_work_ratio;
$legal_year = $legal_week * 52;
$legal_month = $legal_year / 12;

$target_work_ratio = 45/52;

$target_hours = intval($_GET['target_hours'] ?? 13);
$target_week = $target_hours * $legal_work_ratio;
$target_year = $target_week * 52;
$target_month = $target_year / 12;

$user_id = $session->getUser()->id;
$year = (int) ($_GET['year'] ?? null);
$years = $months = $weeks = null;

if ($year) {
	$months = Tracking::listUserMonths($user_id, $year);
	$weeks = Tracking::listUserWeeks($user_id, $year);
}
else {
	$years = Tracking::listUserYears($user_id);
}

$tpl->assign(compact('weeks', 'months', 'years',
	'legal_hours', 'legal_week', 'legal_year', 'legal_month',
	'target_year', 'target_hours', 'target_week', 'target_month'));

$tpl->display(\Paheko\PLUGIN_ROOT . '/templates/year.tpl');
