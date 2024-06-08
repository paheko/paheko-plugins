<?php

namespace Paheko;

use function Paheko\Plugin\Chat\get_channel;

require __DIR__ . '/_inc.php';
$channel = get_channel();

$users = $channel->listUsers();

$tpl = Template::getInstance();
$tpl->assign(compact('users', 'channel'));
$tpl->display(PLUGIN_ROOT . '/templates/users.tpl');
