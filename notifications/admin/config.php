<?php

namespace Paheko;

use Paheko\Users\Session;
use Paheko\Plugin\Notifications\Notifications;

$session = Session::getInstance();
$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$csrf_key = 'plugin_notifications';
$n = new Notifications($plugin);

$form->runIf('add', function () use ($n) {
	$n->add(f('signal'), f('action'), f('config'));
	$n->save();
}, $csrf_key, './config.php?ok');

$form->runIf(qg('delete') !== null, function () use ($n) {
	$n->remove((int)qg('delete'));
	$n->save();
}, null, './config.php?ok');

$tpl->assign([
	'notifications' => $n->list(),
	'signals' => $n::SIGNALS,
	'actions' => $n::ACTIONS,
	'file_contexts' => $n::FILE_CONTEXTS,
]);

$tpl->assign(compact('csrf_key'));

$tpl->display(PLUGIN_ROOT . '/templates/config.tpl');
