<?php

namespace Paheko;

use KD2\Form;

use Paheko\Utils;
use Paheko\Users\Session;

use Paheko\Plugin\Shortcuts\Shortcuts;
use Paheko\Plugin\Shortcuts\Entities\Shortcut;

$session = Session::getInstance();
$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

$csrf_key = 'config_plugin_' . $plugin->id();

$id = Form::getQueryInt('id');

if ($id) {
	$shortcut = Shortcuts::get($id);

	if (!$shortcut) {
		throw new UserException('Ce raccourci n\'existe pas');
	}
}
else {
	$shortcut = Shortcuts::create();
}

$form->runIf('save', function () use ($shortcut) {
	$shortcut->importForm();
	$shortcut->save();
	$shortcut->uploadIcon();
}, $csrf_key, utils::plugin_url() . 'config.php');

$shapes = array_keys(Utils::ICONS);

$tpl->assign(compact('csrf_key', 'shortcut', 'shapes'));

$tpl->display(PLUGIN_ROOT . '/templates/edit.tpl');
