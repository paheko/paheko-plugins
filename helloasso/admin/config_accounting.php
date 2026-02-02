<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\HelloAsso;

require_once __DIR__ . '/_inc.php';

$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$csrf_key = 'hello_config_acc';
$ha = HelloAsso::getInstance();

$form->runIf('save', function () use ($ha) {
	$ha->saveConfig($_POST ?? []);
}, $csrf_key, '?ok');

$plugin_config = $ha->getConfig();

$bank_account = !empty($plugin_config->bank_account_code) ? [$plugin_config->bank_account_code => $plugin_config->bank_account_code] : null;
$provider_account = !empty($plugin_config->provider_account_code) ? [$plugin_config->provider_account_code => $plugin_config->provider_account_code] : null;
$donation_account = !empty($plugin_config->donation_account_code) ? [$plugin_config->donation_account_code => $plugin_config->donation_account_code] : null;

$tpl->assign(compact(
	'csrf_key',
	'plugin_config',
	'bank_account',
	'provider_account',
	'donation_account'
));

$tpl->display(PLUGIN_ROOT . '/templates/config_accounting.tpl');
