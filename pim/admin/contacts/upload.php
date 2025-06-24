<?php

namespace Paheko\Plugin\PIM;
use Paheko\UserException;
use Paheko\Users\Session;

require __DIR__ . '/../_inc.php';

$contacts = new Contacts(Session::getUserId());

$csrf_key = 'upload_file';

$form->runIf('upload', function () use ($contacts) {
	if (empty($_FILES['file']['tmp_name'])) {
		throw new UserException('Erreur à l\'envoi');
	}

	$contacts->importFile($_FILES['file']['tmp_name'], boolval($_POST['archived'] ?? false));
}, $csrf_key, './');

$tpl->assign(compact('csrf_key'));

$tpl->display(__DIR__ . '/../../templates/contacts/upload.tpl');

