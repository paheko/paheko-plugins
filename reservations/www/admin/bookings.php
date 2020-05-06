<?php

namespace Garradin;
use Garradin\Plugin\Reservations\Reservations;

$r = new Reservations;

if (!empty($_GET['delete'])) {
	$r->deleteBooking((int)$_GET['delete']);
	Utils::redirect(Utils::getSelfURL(false));
}
elseif (isset($_POST['book'], $_POST['slot'])) {
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

	$r->createBookingForUser($_POST['slot'], $id_membre, $nom);
	Utils::redirect(Utils::getSelfURL());
}

$tpl->assign('slots', $r->listUpcomingSlots());
$tpl->assign('bookings', $r->listUpcomingBookings());
$tpl->assign('plugin_css', ['style.css']);

$tpl->display(PLUGIN_ROOT . '/templates/admin/bookings.tpl');
