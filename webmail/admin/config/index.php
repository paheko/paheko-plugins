<?php

namespace Paheko;

use Paheko\Plugin\Webmail\Accounts;

require_once __DIR__ . '/_inc.php';

$accounts = Accounts::listWithUserNames();

$tpl->assign(compact('accounts'));

$tpl->display(PLUGIN_ROOT . '/templates/config/index.tpl');
