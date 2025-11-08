<?php

namespace Paheko;

use Paheko\Utils;
use Paheko\Services\Services;
use Paheko\Services\Fees;

if (!$plugin->getConfig('client_id')) {
	Utils::redirect(PLUGIN_ADMIN_URL . 'config.php');
}

$account_obj = (array) $plugin->getConfig('account');
$account_code = strtok(current($account_obj), " ");

$services = Services::listAssoc();
$fees = Fees::listAllByService();
usort($fees, fn($a, $b) => $a->amount - $b->amount);

$base_url = PLUGIN_URL;
$tpl->assign(compact('account_code', 'services', 'fees', 'base_url'));

$tpl->display(__DIR__ . '/../templates/index.tpl');
