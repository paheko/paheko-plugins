<?php

namespace Paheko\Plugin\Invoice;

use Paheko\UserException;
use Paheko\Utils;
use Paheko\Plugin\Invoice\Entities\Client;

use const Paheko\PLUGIN_ROOT;

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
}

$csrf_key = 'edit_client';

$form->runIf('save', function () use ($client) {
	$client->importForm();
	$client->save();
}, $csrf_key, '!p/invoice/clients/');

$tpl->assign(compact('client', 'title', 'csrf_key'));

$tpl->display(PLUGIN_ROOT . '/templates/clients/edit.tpl');
