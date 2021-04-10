<?php

namespace Garradin;

use Garradin\Plugin\Caisse\Session;

require __DIR__ . '/_inc.php';

if (f('start') && f('end')) {
	$start = \DateTime::createFromFormat('d/m/Y', f('start'));
	$end = \DateTime::createFromFormat('d/m/Y', f('end'));
}
else {
	$start = new \DateTime('first day of this month');
	$end = new \DateTime('last day of this month');
}

if ($start && $end && f('export')) {
	Session::exportAccounting($start, $end);
	exit;
}

$tpl->assign(compact('start', 'end'));

$tpl->display(PLUGIN_ROOT . '/templates/export.tpl');