<?php

namespace Garradin\Plugin\Taima;

use Garradin\Plugin\Taima\Tracking;

require_once __DIR__ . '/_inc.php';

$user_id = $session->getUser()->id;
$weeks = Tracking::listUserWeeks($user_id);

$tpl->assign(compact('weeks'));

$tpl->display(\Garradin\PLUGIN_ROOT . '/templates/year.tpl');
