<?php

namespace Garradin;

use Garradin\Plugin\Caisse\Session;
use function Garradin\Plugin\Caisse\get_amount;

require __DIR__ . '/_inc.php';

$pos_session = null;
$csrf_key = 'pos_open_session';

if (null !== qg('id')) {
	$pos_session = new Session((int)qg('id'));
}
elseif ($current_pos_session = Session::getCurrentId()) {
	$pos_session = new Session($current_pos_session);
}

$form->runIf('open', function () use ($session) {
	if (trim(f('amount')) === '') {
		throw new UserException('Le solde de la caisse ne peut être laissé vide.');
	}

	Session::open($session->getUser()->id, get_amount(f('amount')));
}, $csrf_key, Utils::plugin_url(['file' => 'tab.php']));

$tpl->assign(compact('csrf_key', 'pos_session'));

$tpl->assign('current_pos_session', Session::getCurrentId());

if ($pos_session) {
	$tpl->assign('payments', $pos_session->listPayments());
	$tpl->assign('payments_totals', $pos_session->listPaymentTotals());
	$tpl->assign('tabs', $pos_session->listTabsWithItems());
	$tpl->assign('totals_categories', $pos_session->listTotalsByCategory());
	$tpl->assign('total', $pos_session->getTotal());
	$tpl->assign('names', $pos_session->usernames());
	$tpl->assign('missing_users_tabs', $pos_session->listMissingUsers());

	$tpl->assign('title', 'Session de caisse du ' . Utils::date_fr($pos_session->opened));
	$tpl->display(PLUGIN_ROOT . '/templates/session.tpl');
}
else {
	$tpl->display(PLUGIN_ROOT . '/templates/session_open.tpl');
}
