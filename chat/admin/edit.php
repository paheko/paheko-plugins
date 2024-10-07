<?php

namespace Paheko;

use Paheko\Users\Session;

use Paheko\Plugin\Chat\Chat;

$session = Session::getInstance();
$session->requireAccess($session::SECTION_USERS, $session::ACCESS_ADMIN);

$id = intval($_GET['id'] ?? 0);
$csrf_key = 'edit_channel';
$me = Chat::getUser();
$channel = null;

if ($_GET['id'] ?? null) {
	$channel = Chat::getChannel((int)$_GET['id'], $me);
}
else {
	$channel = Chat::createChannel();
}

$form->runIf('save', function () use ($channel) {
	$channel->importForm();
	$channel->save();
	Utils::redirectParent('./?id=' . $channel->id());
}, $csrf_key);

$tpl->assign(compact('channel', 'csrf_key'));

$tpl->display(PLUGIN_ROOT . '/templates/edit.tpl');
