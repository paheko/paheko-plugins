<?php

namespace Paheko;

use Paheko\Users\Session;
use Paheko\Plugin\Chat\Chat;
use function Paheko\Plugin\Chat\get_channel;

require __DIR__ . '/_inc.php';

$session = Session::getInstance();
$channel = get_channel();

$me = $channel->getUser($session);

if ($_GET['with'] ?? null) {
	$channel = Chat::getPMChannel($me, (int)$_GET['with']);

	if (!$channel) {
		throw new ValidationException('No valid channel provided', 400);
	}
}

$csrf_key = 'chat';

$form = new Form;
$tpl->assign_by_ref('form', $form);

$form->runIf('send', function () use ($me, $channel) {
	$channel->say($me, $_POST['text'] ?? '');
}, $csrf_key, '?id=' . $channel->id());

$channels = Chat::listChannels($session);
$messages = $channel->listMessages(null, 50);
$recipient = $channel->getRecipient($me);

$tpl = Template::getInstance();
$tpl->assign(compact('messages', 'channel', 'channels', 'csrf_key', 'recipient', 'me'));
$tpl->display(PLUGIN_ROOT . '/templates/chat.tpl');
