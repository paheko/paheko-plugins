<?php

namespace Paheko;

use Paheko\Users\Session;

use Paheko\Plugin\Chat\Chat;

$id = intval($_GET['id'] ?? 0);
$session = Session::getInstance();

$tpl->assign('custom_css', PLUGIN_URL . 'chat.css');

if ($id) {
	$channel = Chat::getChannel($id);

	if (!$channel) {
		throw new ValidationException('No valid channel provided', 400);
	}

	if ($channel->requiresLogin() && !$session->isLogged()) {
		throw new ValidationException('You cannot access this channel', 403);
	}

	$user = $channel->getUser($session);
	$csrf_key = 'chat';

	$form = new Form;
	$tpl->assign_by_ref('form', $form);

	$form->runIf('send', function () use ($user, $channel) {
		$channel->say($user, $_POST['text'] ?? '');
	}, $csrf_key, '?id=' . $id);

	$channels = Chat::listChannels($session);
	$messages = $channel->listMessages(null, 50);
	$tpl->assign(compact('messages', 'channel', 'channels', 'csrf_key'));
	$tpl->display(PLUGIN_ROOT . '/templates/chat.tpl');
}
else {
	$channels = Chat::listChannels($session);
	$tpl->assign(compact('channels'));
	$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
}


