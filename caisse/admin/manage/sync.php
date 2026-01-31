<?php

namespace Paheko;

use Paheko\Plugin\Caisse\POS;
use Paheko\Users\Session as UserSession;

use Paheko\Accounting\Years;

require __DIR__ . '/_inc.php';

$year = Years::get((int)f('year') ?: (int)qg('year'));
$tpl->assign('year', $year);

$form->runIf($year && f('sync'), function () use ($year) {
	$added = POS::syncAccounting(UserSession::getUserId(), $year);
	Utils::redirect(PLUGIN_ADMIN_URL . 'manage/sync.php?ok=' . $added . '&year=' . $year->id);
});

$tpl->assign('years', Years::listOpenAssoc());

$tpl->display(PLUGIN_ROOT . '/templates/manage/sync.tpl');