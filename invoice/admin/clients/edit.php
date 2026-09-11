<?php

namespace Paheko\Plugin\Invoice;

use Paheko\UserException;
use Paheko\Utils;
use Paheko\Users\Session;

use Paheko\Plugin\Invoice\Entities\Client;

use const Paheko\PLUGIN_ROOT;

Session::getInstance()->requireAccess(Session::SECTION_ACCOUNTING, Session::ACCESS_WRITE);

if (isset($_GET['id'])) {
	$client = Clients::get((int)$_GET['id']);

	if (!$client) {
		throw new UserException('Unknown client ID');
	}

	$title = 'Modifier un client';
}
else {
	$client = new Client;
	$client->created = new \DateTime;
	$title = 'Nouveau client';

	if (!empty($_GET['from_user']) && is_array($_GET['from_user'])) {
		$client->set('id_user', (int) key($_GET['from_user']));
		$client->reloadUserData();
	}
}

$disabled = isset($client->id_user);
$csrf_key = 'edit_client';

$form->runIf('save', function () use ($client) {
	$client->importForm();
	$client->save();
}, $csrf_key, '!p/invoice/clients/');

$tpl->assign(compact('client', 'title', 'csrf_key', 'disabled'));

$tpl->display(PLUGIN_ROOT . '/templates/clients/edit.tpl');
