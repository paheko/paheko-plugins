<?php

namespace Paheko;

use Paheko\Plugin\Caisse\Sessions;
use Paheko\Users\Session;
use Paheko\UserException;

require __DIR__ . '/_inc.php';

$session = Session::getInstance();
$pos_session = Sessions::get((int)qg('id'));

if (!$pos_session) {
	throw new UserException('Aucun numéro de session indiqué, ou numéro invalide');
}

$export = $pos_session->export((bool) qg('details'), qg('pdf') ? 2 : 0);

if (qg('pdf')) {
	return;
}

if ($session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_READ)) {
	if ($session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE) && $plugin->getConfig('accounting_year_id')) {
		$csrf_key = 'sync_pos_' . $pos_session->id();
		$tpl->assign(compact('csrf_key'));

		$form->runIf('sync', function () use ($pos_session, $plugin) {
			$r = $pos_session->syncWithYearId($plugin->getConfig('accounting_year_id'));

			if (!$r) {
				throw new UserException("L'écriture n'a pu être créée, vérifiez que l'exercice sélectionné dans la configuration englobe bien la date de la session de caisse.");
			}
		}, $csrf_key, Utils::getSelfURI());
	}

	$tpl->assign('transaction', $pos_session->getTransaction());
}

$tpl->assign('title', sprintf('Session de caisse n°%d du %s', $pos_session->id(), Utils::date_fr($pos_session->opened)));
$tpl->assign(compact('export'));

$tpl->display(PLUGIN_ROOT . '/templates/session.tpl');
