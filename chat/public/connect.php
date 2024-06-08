<?php

namespace Paheko;

use Paheko\Users\Session;
use Paheko\Plugin\Chat\Chat;

$id = intval($_GET['id'] ?? 0);
$channel = Chat::getChannel($id);

if (!$channel) {
	throw new ValidationException('No valid channel provided', 400);
}

$session = Session::getInstance();

if ($channel->requiresLogin() && !$session->isLogged()) {
	throw new ValidationException('You cannot access this channel', 403);
}

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

if (false === strpos(@ini_get('disable_functions'), 'set_time_limit')) {
	@set_time_limit(600);
}

@ini_set('max_execution_time', 600);
ignore_user_abort(true);

$started = time();
$last_seen = intval($_GET['last_seen'] ?? 0);
$user = $channel->getUser($session);

while (true) {
	$elapsed = time() - $started;

file_put_contents(DATA_ROOT . '/lol', '.', FILE_APPEND);
	// Stop loop if connection is closed, or if time is running out
	if (connection_aborted() || $elapsed >= 590) {
file_put_contents(DATA_ROOT . '/lol', '+', FILE_APPEND);
		break;
	}

	$refresh = false;

	foreach ($channel->getEventsSince($last_seen, $user) as $event) {
		echo "event: " . $event['type'] . "\r\n";
		echo "data: " . json_encode($event) . "\r\n\r\n";
	}

	if (!$refresh && $last_seen % 3 == 0) {
		echo ": ping\r\n\r\n";
	}

	ob_flush();
	flush();

	$last_seen = time();
	sleep(1);
}

file_put_contents(DATA_ROOT . '/lol', '!!!', FILE_APPEND);

$user->disconnect();
