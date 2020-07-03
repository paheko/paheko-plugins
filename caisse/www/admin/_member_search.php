<?php

namespace Garradin;

use Garradin\Plugin\Caisse\Tab;


$tpl->assign('list', Tab::searchMember($_GET['q']));

$tpl->display(PLUGIN_ROOT . '/templates/_member_search.tpl');