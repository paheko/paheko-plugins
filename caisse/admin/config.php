<?php

namespace Paheko;

use Paheko\Accounting\Years;

require_once __DIR__ . '/_inc.php';

$csrf_key = 'caisse_config';

$form->runIf('save', function () use ($plugin) {
	$plugin->setConfigProperty('allow_custom_user_name', (bool)f('allow_custom_user_name'));
	$plugin->setConfigProperty('send_email_when_closing', trim(f('send_email_when_closing') ?: '') ?: null);
	$plugin->setConfigProperty('auto_close_tabs', (bool)f('auto_close_tabs'));
	$plugin->setConfigProperty('accounting_year_id', intval(f('accounting_year_id')) ?: null);
	$plugin->setConfigProperty('force_tab_name', (bool)f('force_tab_name'));
	$plugin->save();
}, $csrf_key, PLUGIN_ADMIN_URL . 'config.php?ok');

$years = Years::listOpenAssoc();
$tpl->assign(compact('csrf_key', 'years'));

$tpl->display(PLUGIN_ROOT . '/templates/config.tpl');
