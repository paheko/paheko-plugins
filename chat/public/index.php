<?php

namespace Paheko;

use Paheko\Users\Session;
use Paheko\Plugin\Chat\Chat;
use function Paheko\Plugin\Chat\get_channel;

require __DIR__ . '/_inc.php';

$me = Chat::getUser();

$channel = null;

if ($_GET['id'] ?? null) {
	$channel = Chat::getChannel((int)$_GET['id'], $me);
}
elseif ($_GET['with'] ?? null) {
	$channel = Chat::getDirectChannel($me, (int)$_GET['with']);

	if (!$channel) {
		throw new ValidationException('No valid channel provided', 400);
	}
}
else {
	$channel = Chat::getFallbackChannel($me);
}

if (!$channel) {
	if ($session->isLogged()) {
		Utils::redirect('!p/chat/edit.php');
	}

	throw new UserException('Vous n\'avez accès à aucune discussion.', 404);
}

$channel->join($me);

$csrf_key = 'chat';

$form = new Form;
$tpl->assign_by_ref('form', $form);

$form->runIf('send', function () use ($me, $channel) {
	$channel->say($me, $_POST['send'] ?? '');
}, $csrf_key, '?id=' . $channel->id());

$channels = Chat::listChannels($session);
$messages = $channel->listMessages(null, 50);
$recipient = $channel->getRecipient($me);

$tpl = Template::getInstance();
$tpl->assign(compact('messages', 'channel', 'channels', 'csrf_key', 'recipient', 'me'));
$tpl->display(PLUGIN_ROOT . '/templates/chat.tpl');
