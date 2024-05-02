<?php

namespace Paheko;

use Paheko\Config;
use Paheko\Utils;

$csrf_key = 'plugin_usermap';

$usermap = new \Paheko\Plugin\Usermap\Usermap;
$config = Config::getInstance();

$form->runIf('sync', function () use ($usermap, $plugin) {
	$last = $plugin->getConfig('last');

	if ($last && $last > time() - 3600*18) {
		throw new UserException('Cette action est limitée à une fois par jour pour réduire la charge du serveur. Merci d\'attendre 24 heures avant de réessayer.');
	}

	$count = $usermap->syncUserLocations();
	$plugin->setConfigProperty('last', time());
	$plugin->save();

	if (null === $count) {
		Utils::redirect($plugin->url('admin/?msg=NOTHING'));
	}
	Utils::redirect($plugin->url('admin/?msg=' . $count));
}, $csrf_key);

$address = $_GET['address'] ?? $config->org_address;
$address = $usermap->normalizeAddress($address);

$tpl->assign('missing_users_count', $usermap->countMissingUsers());
$tpl->assign('stats', $address ? $usermap->getDistanceStatsTo($address) : null);
$tpl->assign(compact('csrf_key', 'address'));

$tpl->display(__DIR__ . '/../templates/index.tpl');
