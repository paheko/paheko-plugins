<?php

namespace Garradin;

use function Garradin\Plugin\Caisse\get_amount;

define('SESSION_CREATE', true);
require __DIR__ . '/_inc.php';

$s = new Plugin\Caisse\Session;

if (!empty($_POST['open'])) {
	$s->open($session->getUser()->id, get_amount(f('amount')));
	Utils::redirect(Utils::plugin_url(['file' => 'tab.php']));
}
elseif (!empty($_POST['close'])) {
	$s->close($pos_session->id);
}

$tpl->assign('pos_session', $pos_session);

if ($pos_session) {
	$tpl->assign('payments', $s->listPayments($pos_session->id));
	$tpl->assign('payments_totals', $s->listPaymentTotals($pos_session->id));
	$tpl->assign('tabs', $s->listTabsTotals($pos_session->id));

	$tpl->display(PLUGIN_ROOT . '/templates/session.tpl');
}
else {
	$tpl->display(PLUGIN_ROOT . '/templates/session_open.tpl');
}
