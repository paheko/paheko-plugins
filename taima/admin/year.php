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

$tpl->assign(compact('weeks', 'months', 'years'));

$tpl->display(\Paheko\PLUGIN_ROOT . '/templates/year.tpl');
