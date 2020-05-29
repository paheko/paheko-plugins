<?php

namespace Garradin;
use Garradin\Plugin\Reservations\Reservations;

if ($plugin->needUpgrade()) {
	$plugin->upgrade();
}

$r = new Reservations;

if (!empty($_GET['delete'])) {
	$r->deleteBooking((int)$_GET['delete']);
	utils::redirect(utils::plugin_url(['file' => 'bookings.php', 'query' => sprintf('cat=%d', qg('cat'))]));
}
elseif (isset($_POST['book'], $_POST['slot'])) {
	$r->createBookingForUser($_POST['slot'], f('nom'), f('champ'));
	Utils::redirect(Utils::getSelfURL());
}

$categories = $r->listCategories();
$cat = null;

if (count($categories) == 1) {
	$cat = current($categories);
}
elseif (qg('cat')) {
	$cat = $r->getCategory(qg('cat'));

	if (!$cat) {
		throw new UserException('Catégorie inconnue');
	}
}
else {
	$tpl->assign('categories', $categories);
}

$tpl->assign('cat', $cat);

if ($cat) {
	$tpl->assign('slots', $r->listUpcomingSlots($cat->id));
	$tpl->assign('bookings', $r->listUpcomingBookings($cat->id));
}

$tpl->assign('plugin_css', ['style.css']);

$tpl->display(PLUGIN_ROOT . '/templates/admin/bookings.tpl');
