<?php

namespace Garradin;
use Garradin\Plugin\Caisse\Session;

require __DIR__ . '/_inc.php';

$tpl->assign('current_pos_session', Session::getCurrentId());
$tpl->assign('pos_sessions', Session::list());
$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
