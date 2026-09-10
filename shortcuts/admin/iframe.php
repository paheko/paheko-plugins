<?php

namespace Paheko;

use KD2\Form;

use Paheko\Users\Session;

use Paheko\Plugin\Shortcuts\Shortcuts;

$session = Session::getInstance();
$session->requireAccess($session::SECTION_CONNECT, $session::ACCESS_READ);

$id = Form::getQueryInt('id');
$shortcut = Shortcuts::get($id ?? 0);

if (!$shortcut) {
	throw new UserException('Ce raccourci n\'existe pas', 404);
}

$tpl = Template::getInstance();

$tpl->assign(compact('shortcut'));

$tpl->display(PLUGIN_ROOT . '/templates/iframe.tpl');
