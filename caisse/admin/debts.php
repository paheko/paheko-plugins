<?php

namespace Paheko;

use Paheko\Users\Session;
use Paheko\Plugin\Caisse\Tabs;

require __DIR__ . '/_inc.php';

$list = Tabs::listDebts();
$list->loadFromQueryString();
$tpl->assign('list', $list);

$tpl->display(PLUGIN_ROOT . '/templates/debts.tpl');
