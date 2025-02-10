<?php

namespace Paheko\Plugin\PIM;

use Paheko\Users\Session;

require __DIR__ . '/../_inc.php';

$tpl->assign('dav_url', $plugin->url());

$tpl->display(__DIR__ . '/../../templates/config/index.tpl');
