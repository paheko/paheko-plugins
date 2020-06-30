<?php

namespace Garradin;

use Garradin\Plugin\Caisse\Session;
use function Garradin\Plugin\Caisse\get_amount;

require __DIR__ . '/_inc.php';

$pos_session = null;

if (null !== qg('id')) {
	$pos_session = new Session((int)qg('id'));
}
elseif ($current_pos_session = Session::getCurrentId()) {
	$pos_session = new Session($current_pos_session);
}

if (!empty($_POST['open'])) {
	if (trim(f('amount')) === '') {
		throw new UserException('Le solde de la caisse ne peut être laissé vide.');
	}
	Session::open($session->getUser()->id, get_amount(f('amount')));
	Utils::redirect(Utils::plugin_url(['file' => 'tab.php']));
}

$tpl->assign('current_pos_session', Session::getCurrentId());
$tpl->assign('pos_session', $pos_session);

if ($pos_session) {
	$tpl->assign('payments', $pos_session->listPayments());
	$tpl->assign('payments_totals', $pos_session->listPaymentTotals());
	$tpl->assign('tabs', $pos_session->listTabsWithItems());
	$tpl->assign('totals_categories', $pos_session->listTotalsByCategory());
	$tpl->assign('total', $pos_session->getTotal());

	$tpl->assign('title', 'Session de caisse du ' . Utils::sqliteDateToFrench($pos_session->opened));
	$tpl->display(PLUGIN_ROOT . '/templates/session.tpl');
}
else {
	$tpl->display(PLUGIN_ROOT . '/templates/session_open.tpl');
}
