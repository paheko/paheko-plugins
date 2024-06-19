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
$current_day = ($_GET['current_day'] ?? null) ?: null;
$current_user = ($_GET['current_user'] ?? null) ?: null;

while (true) {
	$elapsed = time() - $started;
	file_put_contents(DATA_ROOT . '/lol', '.', FILE_APPEND);

	// Stop loop if connection is closed, or if time is running out
	if (connection_aborted() || $elapsed >= 590) {
		file_put_contents(DATA_ROOT . '/lol', '_', FILE_APPEND);
		break;
	}

	$refresh = false;

	foreach ($channel->getEventsSince($last_seen_ts, $last_seen_id, $me) as $event) {
		if ($event['type'] === 'message') {
			$event['data']['html'] = chat_message_html($event['data']['message'], $me);
		}

		echo "event: " . $event['type'] . "\r\n";
		echo "data: " . json_encode($event['data']) . "\r\n\r\n";
		$last_seen_id = max($last_seen_id, $event['data']['message']->id);
		$refresh = true;
	}

	if ($refresh) {
		$last_seen_ts = time();
	}

	// This seems to be required to make connection_aborted() work
	if (!$refresh && $elapsed % 5 == 0) {
		echo ": ping\r\n\r\n";
	}

	ob_flush();
	flush();

	usleep(500000);
}

file_put_contents(DATA_ROOT . '/lol', '!!!', FILE_APPEND);

$me->disconnect();
