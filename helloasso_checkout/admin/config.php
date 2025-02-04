<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso_Checkout\API;

$csrf_key = 'plugin_helloasso_checkout';

$client_id = $plugin->getConfig('client_id') ?? "";
$client_secret = $plugin->getConfig('client_secret') ?? "";
$sandbox = $plugin->getConfig('sandbox') ?? "";
$account = $plugin->getConfig('account') ?? "";
$error = null;

$form->runIf('save', function () use ($plugin, &$client_id, &$client_secret, &$sandbox, &$account, &$error) {
	if ($account != f('account')) {
		$account = f('account');
		$plugin->setConfigProperty('account', $account);
		$plugin->save();
	}

	if ($sandbox != (int) f('sandbox')) {
		$sandbox = (int) f('sandbox');
		$plugin->setConfigProperty('sandbox', $sandbox);
		$plugin->save();
	}

	if (($client_id != f('client_id') || $client_secret != f('client_secret')) && (empty($client_secret) || !empty(f('client_secret')))) {
		$client_id = f('client_id');
		$client_secret = f('client_secret');

		API::getInstance()->register($client_id, $client_secret);

		Extensions::toggle('helloasso_checkout_snippets', true);
	}

	Utils::redirect(PLUGIN_ADMIN_URL);
}, $csrf_key);

$tpl->assign(compact('csrf_key', 'client_id', 'sandbox', 'account', 'error'));

$tpl->display(__DIR__ . '/../templates/config.tpl');
