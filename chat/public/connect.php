<?php

namespace Paheko;

use Paheko\Users\Session;
use Paheko\Plugin\Chat\Chat;

use function Paheko\Plugin\Chat\chat_message_html;

require __DIR__ . '/_inc.php';

$me = Chat::getUser();

if (!$me) {
	throw new ValidationException('Access restricted', 401);
}

$id = intval($_GET['id'] ?? 0);
$channel = Chat::getChannel($id, $me);

if (!$channel) {
	throw new ValidationException('No valid channel provided', 404);
}

@ob_end_clean();

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

if (false === strpos(@ini_get('disable_functions'), 'set_time_limit')) {
	@set_time_limit(600);
}

@ini_set('max_execution_time', 600);
ignore_user_abort(true);

$started = time();
$last_seen_ts = intval($_GET['last_seen'] ?? time());
$last_seen_id = intval($_GET['last_seen_id'] ?? 0);
$last_seen_message_id = $channel->join($me);

while (true) {
	$elapsed = time() - $started;

	// Stop loop if connection is closed, or if time is running out
	if (connection_aborted() || $elapsed >= 590) {
		break;
	}

	//$refresh = false;
	$refresh = true;

	foreach ($channel->getEventsSince($last_seen_ts, $last_seen_id, $me) as $event) {
		if ($event['type'] === 'message') {
			$event['data']['html'] = chat_message_html($event['data']['message'], $me);
			$last_seen_id = max($last_seen_id, $event['data']['message']->id);
		}

		echo "event: " . $event['type'] . "\r\n";
		echo "data: " . json_encode($event['data']) . "\r\n\r\n";
		//$refresh = true;
	}

	// TODO: add events for list of chats the user is part of (left-side pane),
	// so that the user sees if a new DM chat is open

	if ($refresh) {
		$last_seen_ts = time();
	}

	// This seems to be required to make connection_aborted() work
	if ($elapsed % 5 == 0) {
		echo ": ping\r\n\r\n";
	}

	@ob_flush();
	@flush();

	// Sleep for 500 ms (0.5 second)
	usleep(500000);
}

$me->disconnect();
$channel->prune();
