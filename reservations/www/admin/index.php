<?php

namespace Garradin;
use Garradin\Plugin\Reservations\Reservations;


$r = new Reservations;

$user = $session->getUser();

if (isset($_POST['book'], $_POST['slot'])) {
	$r->createUserBooking($_POST['slot'], $user->id, null);
	Utils::redirect(Utils::getSelfURL());
}
elseif (isset($_POST['cancel'])) {
	$r->cancelUserBooking();
	Utils::redirect(Utils::getSelfURL());
}

$tpl->assign('slots', $r->listUpcomingSlots());
$tpl->assign('booking', $r->getUserBooking());
$tpl->assign('config', $plugin->getConfig());
$tpl->assign('plugin_css', ['style.css']);
$tpl->assign('custom_css', ['wiki.css']);

$tpl->display(PLUGIN_ROOT . '/templates/admin/index.tpl');
