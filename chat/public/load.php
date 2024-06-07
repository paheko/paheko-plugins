<?php

namespace Paheko;

use Paheko\Users\Session;

use Paheko\Plugin\Chat\Chat;

$id = intval($_GET['channel'] ?? 0);
$channel = Chat::getChannel($id);
$session = Session::getInstance();

if (!$channel) {
	throw new ValidationException('No valid channel provided', 400);
}

if ($channel->requiresLogin() && !$session->isLogged()) {
	throw new ValidationException('You cannot access this channel', 403);
}

$users = $channel->listUsers();
$messages = $channel->listMessages();

header('Content-Type: application/json; charset=utf-8');

echo json_encode(compact('users', 'messages'));
