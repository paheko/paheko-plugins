<?php

namespace Garradin;

use Garradin\Plugin\Caisse\POS;
use Garradin\Membres\Session as UserSession;

use Garradin\Accounting\Years;

require __DIR__ . '/_inc.php';

$year = f('year') ? Years::get((int)f('year')) : null;
$tpl->assign('year', $year);

$form->runIf($year && f('sync'), function () use ($year) {
	$added = POS::syncAccounting(UserSession::getInstance()->getUser()->id, $year);
	Utils::redirect(PLUGIN_URL . 'manage/sync.php?ok=' . $added);
});

$tpl->assign('years', Years::listOpenAssoc());

$tpl->display(PLUGIN_ROOT . '/templates/manage/sync.tpl');