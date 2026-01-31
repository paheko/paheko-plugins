<?php

namespace Paheko;

use Paheko\Users\Session;
use Paheko\Plugin\Caisse\Sessions;
use function Paheko\Plugin\Caisse\get_amount;

require __DIR__ . '/_inc.php';

$pos_session = Sessions::get((int)qg('id'));

if (!$pos_session) {
	throw new UserException('Numéro de session inconnu');
}

if ($pos_session->closed) {
	throw new UserException('Cette session est déjà clôturée');
}

$url = Utils::plugin_url(['file' => 'session.php', 'query' => 'id=' . $pos_session->id]);
$csrf_key = 'pos_close_' . $pos_session->id();

$form->runIf('close', function () use ($session, $pos_session, $plugin) {
	if (empty($_POST['confirm'])) {
		throw new UserException('Merci de valider que les informations du formulaire sont justes.');
	}

	if (!isset($_POST['balances']) || !is_array($_POST['balances'])) {
		$_POST['balances'] = [];
	}

	if (!isset($_POST['payments']) || !is_array($_POST['payments'])) {
		$_POST['payments'] = [];
	}

	$name = $_POST['user_name'] ?? $session->getUser()->name();

	$pos_session->close($name, $_POST['balances'], array_keys($_POST['payments']));

	if ($id = $plugin->getConfig('accounting_year_id')) {
		$pos_session->syncWithYearId($id, Session::getUserId());
	}

	if ($email = $plugin->getConfig('send_email_when_closing')) {
		$pos_session->sendTo($email);
	}

}, $csrf_key, $url);

$tpl->assign('open_notes', $pos_session->hasOpenNotes());

$tpl->assign('user_name', $session->getUser()->name());
$tpl->assign('payments_except_cash', $pos_session->listTrackedPayment());
$tpl->assign('missing_users', $pos_session->listTabIdsWithFeesButNoUser());

$tpl->assign('title', sprintf('Clôture — Session n°%d du %s', $pos_session->id(), Utils::date_fr($pos_session->opened)));

$balances = $pos_session->listClosingBalances();

$tpl->assign(compact('csrf_key', 'pos_session', 'balances'));

$tpl->display(PLUGIN_ROOT . '/templates/session_close.tpl');
