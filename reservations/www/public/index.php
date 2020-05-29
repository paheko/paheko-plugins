<?php

namespace Garradin;

use Garradin\Plugin\Reservations\Reservations;

if ($plugin->needUpgrade()) {
	$plugin->upgrade();
}

$tpl = Template::getInstance();

$r = new Reservations;

if (isset($_POST['book'], $_POST['slot'])) {
	$nom = substr(trim($_POST['nom']), 0, 100);
	$champ = isset($_POST['champ']) ? substr(trim($_POST['champ']), 0, 100) : null;

	$r->createUserBooking($_POST['slot'], $nom, $champ);
	Utils::redirect(Utils::getSelfURL());
}
elseif (isset($_POST['cancel'])) {
	$r->cancelUserBooking();
	Utils::redirect(Utils::getSelfURL());
}

$booking = $r->getUserBooking();
$cat_id = $cat = null;

if ($booking) {
	$cat_id = $booking->categorie;
}
elseif (!empty($_GET['cat'])) {
	$cat_id = (int) $_GET['cat'];
}
else {
	$categories = $r->listCategories();

	if (count($categories) == 1) {
		$cat_id = current($categories)->id;
	}
}

if ($cat_id) {
	$cat = $r->getCategory($cat_id);

	if (!$cat) {
		throw new UserException('Catégorie inconnue');
	}
}

if (!$cat) {
	$tpl->assign('categories', $categories);
}
else {
	$tpl->assign('slots', $r->listUpcomingSlots($cat->id));
}

$tpl->assign('cat', $cat);
$tpl->assign('booking', $booking);
$tpl->assign('plugin_tpl', PLUGIN_ROOT . '/templates');
$tpl->assign('css', file_get_contents(PLUGIN_ROOT . '/www/admin/style.css'));

$tpl->assign('config', Config::getInstance()->getConfig());

$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
