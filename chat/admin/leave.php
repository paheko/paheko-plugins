<?php

namespace Paheko;

use Paheko\Plugin\Chat\Chat;

$csrf_key = 'leave_channel';
$me = Chat::getUser();
$channel = Chat::getChannel((int)($_GET['id'] ?? 0), $me);

if (!$channel) {
	throw new UserException('Discussion inconnue');
}

$channel->leave($me);
Utils::redirect('./');
