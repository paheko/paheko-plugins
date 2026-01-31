<?php

namespace Paheko;

use Paheko\Config;

$usermap = new \Paheko\Plugin\Usermap\Usermap;

$address = $_GET['address'] ?? Config::getInstance()->org_address;
$address = $usermap->normalizeAddress($address);

$tpl->assign('plugin_css', ['./leaflet/leaflet.css']);
$tpl->assign('list', $usermap->listCoordinates());
$tpl->assign('center', $usermap->getLatLon($address ?: '5 rue du Havre, Dijon'));
$tpl->assign('count', $usermap->count());

$tpl->display(__DIR__ . '/../templates/map.tpl');
