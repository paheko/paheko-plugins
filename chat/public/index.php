<?php

namespace Paheko;

use Paheko\Users\Session;
use Paheko\Plugin\Chat\Chat;

require __DIR__ . '/_inc.php';

$session = Session::getInstance();
$me = Chat::getUser();

if (!$me) {
	Utils::redirect('./ask_nick.php');
}

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

$is_admin = $session->canAccess($session::SECTION_USERS, $session::ACCESS_ADMIN);

$form->runIf('delete_message', function () use ($me, $is_admin) {
	$message = Chat::getMessage((int)$_POST['delete_message'], $is_admin ? null : $me);
	$message->markAsDeleted();
}, $csrf_key, './?id=' . $channel->id());

$form->runIf('send', function () use ($me, $channel) {
	if (isset($_POST['message'])) {
		$channel->say($me, $_POST['message']);
	}
	elseif (isset($_POST['comment'])) {
		$channel->comment($me, $_POST['comment']);
	}
	elseif (isset($_FILES['audio'])) {
		$channel->uploadRecording($me, 'audio');
	}
	elseif (isset($_FILES['file'])) {
		$channel->uploadFile($me, 'file');
	}
	elseif (isset($_POST['reaction_message_id'], $_POST['reaction_emoji'])) {
		$channel->reactTo($me, (int)$_POST['reaction_message_id'], $_POST['reaction_emoji']);
	}
}, $csrf_key, './?id=' . $channel->id());

$focus = $_GET['focus'] ?? null;

if ($focus) {
	$focus = (int)$focus;
}

$channels = Chat::listChannels($me);
$messages = $channel->listMessages($focus, 100);
$recipient = $channel->getRecipient($me);

$tpl = Template::getInstance();
$tpl->assign(compact('messages', 'channel', 'channels', 'csrf_key', 'recipient', 'me'));
$tpl->display(PLUGIN_ROOT . '/templates/chat.tpl');

if (time() % 10 == 0) {
	Chat::pruneAnonymousUsers();
}
