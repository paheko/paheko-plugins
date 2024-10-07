<?php

namespace Paheko;

use Paheko\Plugin\Chat\Chat;
use function Paheko\Plugin\Chat\get_channel;

require __DIR__ . '/_inc.php';

$me = Chat::getUser();
$channel = Chat::getChannel(intval($_GET['id'] ?? 0), $me);

$users = $channel->listUsers();

$tpl = Template::getInstance();
$tpl->assign(compact('users', 'channel'));
$tpl->display(PLUGIN_ROOT . '/templates/users.tpl');
