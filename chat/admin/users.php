<?php

namespace Paheko;

use Paheko\Users\Session;
use Paheko\Plugin\Chat\Chat;
use function Paheko\Plugin\Chat\get_channel;

require __DIR__ . '/../public/_inc.php';

$session = Session::getInstance();
$session->requireAccess($session::SECTION_USERS, $session::ACCESS_ADMIN);

$me = Chat::getUser();
$channel = Chat::getChannel(intval($_GET['id'] ?? 0), $me);

$users = $channel->listUsers();

$tpl = Template::getInstance();
$tpl->assign(compact('users', 'channel', 'plugin'));
$tpl->display(PLUGIN_ROOT . '/templates/users.tpl');
