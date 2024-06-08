<?php

namespace Paheko;

use Paheko\Users\Session;

use Paheko\Plugin\Chat\Chat;

$id = intval($_GET['id'] ?? 0);
$session = Session::getInstance();
$channel = null;

$tpl->assign('custom_css', PLUGIN_URL . 'chat.css');

if ($id) {
	$channel = Chat::getChannel($id, $session);
}

if (!$channel) {
	$channel = Chat::getFallbackChannel($session);
}

if (!$channel) {
	if ($session->isLogged()) {
		Utils::redirect('./edit.php');
	}

	throw new ValidationException('No valid channel provided', 400);
}

$me = $channel->getUser($session);
$csrf_key = 'chat';

$form = new Form;
$tpl->assign_by_ref('form', $form);

$form->runIf('send', function () use ($me, $channel) {
	$channel->say($me, $_POST['text'] ?? '');
}, $csrf_key, '?id=' . $id);

$channels = Chat::listChannels($session);
$messages = $channel->listMessages(null, 50);
$tpl->assign(compact('messages', 'channel', 'channels', 'csrf_key'));
$tpl->display(PLUGIN_ROOT . '/templates/chat.tpl');
