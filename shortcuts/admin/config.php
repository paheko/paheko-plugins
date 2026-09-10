<?php

namespace Paheko;

use KD2\Form;

use Paheko\Users\Session;

use Paheko\Plugin\Shortcuts\Shortcuts;

$session = Session::getInstance();
$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$csrf_key = 'config_plugin_' . $plugin->id();

$form->runIf('save', function () {
	$order = Form::getPostArray('sort_order');
	Shortcuts::saveSortOrder($order);
}, $csrf_key, '?msg=SAVED');

$list = Shortcuts::list();

$tpl->assign(compact('csrf_key', 'list'));

$tpl->display(PLUGIN_ROOT . '/templates/config.tpl');
