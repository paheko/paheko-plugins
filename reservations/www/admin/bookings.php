<?php

namespace Garradin;
use Garradin\Plugin\Reservations\Reservations;

$r = new Reservations;

$tpl->assign('bookings', $r->listUpcomingBookings());
$tpl->assign('plugin_css', ['style.css']);

$tpl->display(PLUGIN_ROOT . '/templates/admin/bookings.tpl');
