<?php

namespace Paheko\Plugin\PIM;

use Paheko\Users\Session;

require __DIR__ . '/../_inc.php';

$pim = new PIM(Session::getUserId());
$dav = null;

$form->runIf('generate', function () use ($pim, $plugin, &$dav) {
	$dav = $pim->generateDAVCredentials($plugin);
});

$dav ??= $pim->getDAVCredentials($plugin);

$tpl->assign(compact('dav'));

$tpl->display(__DIR__ . '/../../templates/config/index.tpl');
