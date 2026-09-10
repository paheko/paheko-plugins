<?php

namespace Paheko;

use KD2\Form;

use Paheko\Utils;
use Paheko\Users\Session;

use Paheko\Plugin\Shortcuts\Shortcuts;

$session = Session::getInstance();
$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$csrf_key = 'config_plugin_' . $plugin->id();

$id = Form::getQueryInt('id');
$shortcut = Shortcuts::get($id ?? 0);

if (!$shortcut) {
	throw new UserException('Ce raccourci n\'existe pas');
}

$form->runIf('delete', function () use ($shortcut) {
	$shortcut->delete();
}, $csrf_key, Utils::plugin_url() . 'config.php');

$tpl->assign(compact('csrf_key', 'shortcut'));

$tpl->display(PLUGIN_ROOT . '/templates/delete.tpl');
