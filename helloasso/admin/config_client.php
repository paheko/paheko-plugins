<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\HelloAsso;

$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$csrf_key = sprintf('config_plugin_%s', $plugin->id);

$ha = HelloAsso::getInstance();

$form->runIf('save', function () use ($ha) {
	$ha->saveClient($_POST['client_id'] ?? '', $_POST['client_secret'] ?? '', (bool) ($_POST['sandbox'] ?? false));
}, $csrf_key, './sync.php?msg=CONNECTED');

$tpl->assign([
	'client_id'  => $ha->getClientId(),
	'secret'     => '',
	'csrf_key'   => $csrf_key,
	'sandbox'    => $ha->getConfig()->sandbox ?? false,
]);

$tpl->display(PLUGIN_ROOT . '/templates/config_client.tpl');
