<?php

namespace Paheko;

use Paheko\Plugin\Caisse\Tabs;

$tpl->assign('list', Tabs::searchUserWithServices($_GET['q']));

$tpl->display(PLUGIN_ROOT . '/templates/user_search.tpl');