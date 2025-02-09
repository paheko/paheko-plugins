<?php

namespace Paheko\Plugin\PIM;

use Paheko\UserException;
use Paheko\Utils;
use Paheko\Users\Session;

require __DIR__ . '/_inc.php';

$events = new Events(Session::getUserId());

$csrf_key = 'upload_file';

$form->runIf('upload', function () use ($events) {
	if (empty($_FILES['file']['tmp_name'])) {
		throw new UserException('Erreur à l\'envoi');
	}

	$events->importFile($_FILES['file']['tmp_name']);
}, $csrf_key, './');

$tpl->assign(compact('csrf_key'));

$tpl->display(__DIR__ . '/../templates/upload.tpl');
