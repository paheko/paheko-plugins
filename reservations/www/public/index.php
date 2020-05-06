<?php

namespace Garradin;

use Garradin\Plugin\Reservations\Reservations;

$tpl = Template::getInstance();

$r = new Reservations;

if (isset($_POST['book'], $_POST['slot'])) {
	if (!empty($_POST['numero'])) {
		$id_membre = (new Membres)->getIDWithNumero((int)$_POST['numero']);
		$nom = null;

		if (!$id_membre) {
			throw new UserException('Numéro de membre inconnu');
		}
	}
	else {
		$nom = substr(trim($_POST['nom']), 0, 100);
		$id_membre = null;
	}

	$r->createUserBooking($_POST['slot'], $id_membre, $nom);
	Utils::redirect(Utils::getSelfURL());
}
elseif (isset($_POST['cancel'])) {
	$r->cancelUserBooking();
	Utils::redirect(Utils::getSelfURL());
}

$tpl->assign('slots', $r->listUpcomingSlots());
$tpl->assign('booking', $r->getUserBooking());
$tpl->assign('config', $plugin->getConfig());
$tpl->assign('plugin_tpl', PLUGIN_ROOT . '/templates');
$tpl->assign('css', file_get_contents(PLUGIN_ROOT . '/www/admin/style.css'));

$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
