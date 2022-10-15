<?php

namespace Garradin\Plugin\Taima;

use Garradin\Plugin\Taima\Tracking;
use Garradin\Accounting\Years;
use Garradin\Utils;
use Garradin\UserException;
use Garradin\Users\Session;

use function Garradin\{f, qg};

require_once __DIR__ . '/_inc.php';

$session = Session::getInstance();
$session->requireAccess($session::SECTION_USERS, $session::ACCESS_ADMIN);
$session->requireAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE);

$csrf_key = 'taima_report';
$year = Years::getCurrentOpenYear();

if (!$year) {
	throw new UserException('Aucun exercice n\'est ouvert, il n\'est donc pas possible de valoriser le temps bénévole.');
}

$start = f('start') ? Utils::get_datetime(f('start')) : $year->start_date;
$end = f('end') ? Utils::get_datetime(f('end')) : $year->end_date;

$form->runIf('save', function () use ($year, $start, $end) {
	$id_user = Session::getUserId();
	$t = Tracking::createReport($year, $start, $end, $id_user);
	$t->save();
	Utils::redirect(Utils::getSelfURI(['ok' => $t->id()]));
}, $csrf_key);

$report = Tracking::getFinancialReport($year, $start, $end);

$tpl->assign(compact('report', 'year', 'csrf_key'));

$tpl->display(__DIR__ . '/../templates/report.tpl');
