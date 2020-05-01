<?php

namespace Garradin;
use Garradin\Plugin\Reservations\Reservations;

$r = new Reservations;

if (!empty($_GET['delete'])) {
	$r->deleteBooking((int)$_GET['delete']);
	Utils::redirect(Utils::getSelfURL(false));
}

$tpl->assign('bookings', $r->listUpcomingBookings());
$tpl->assign('plugin_css', ['style.css']);

$tpl->display(PLUGIN_ROOT . '/templates/admin/bookings.tpl');
