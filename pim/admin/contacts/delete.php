<?php

namespace Paheko\Plugin\PIM;

use Paheko\UserException;
use Paheko\Users\Session;
use Paheko\Utils;

require __DIR__ . '/../_inc.php';

$contacts = new Contacts(Session::getUserId());

$id = intval($_GET['id'] ?? 0);
$contact = $contacts->get($id);

if (!$contact) {
	throw new UserException('Contact introuvable');
}

$csrf_key = 'pim_contact_delete';

$form->runIf('delete', function () use ($contact) {
	$contact->delete();
	Utils::redirectParent('./');
}, $csrf_key);

$tpl->assign(compact('contact', 'csrf_key'));

$tpl->display(__DIR__ . '/../../templates/contacts/delete.tpl');
