<?php

namespace Paheko;

use Paheko\Users\Session;
use Paheko\Plugin\Chat\Chat;

require __DIR__ . '/_inc.php';

if (!Chat::hasPublicChannels()) {
	throw new UserException('Aucune discussion disponible', 403);
}

$csrf_key = 'ask_nick';

$form = new Form;
$tpl->assign_by_ref('form', $form);

$form->runIf('save', function () {
	Chat::createAnonymousUser($_POST['name'] ?? '');
}, $csrf_key, './');

$tpl = Template::getInstance();
$tpl->assign(compact('csrf_key'));
$tpl->display(PLUGIN_ROOT . '/templates/ask_nick.tpl');
