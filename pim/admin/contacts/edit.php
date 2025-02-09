<?php

namespace Paheko\Plugin\PIM;

use Paheko\UserException;
use Paheko\Utils;
use Paheko\Plugin\PIM\Entities\Contact;
use Paheko\Users\Session;

require __DIR__ . '/../_inc.php';

$contacts = new Contacts(Session::getUserId());

if ($id = intval($_GET['id'] ?? 0)) {
	$contact = $contacts->get($id);

	if (!$contact) {
		throw new UserException('Contact inconnu');
	}
}
else {
	$contact = new Contact;
	$contact->id_user = $user_id;
}

$csrf_key = 'pim_contact_edit';

$form->runIf('save', function () use ($contact, $plugin) {
	$contact->importForm();

	if (!empty($_FILES['photo']['name'])) {
		$contact->uploadPhoto($plugin->storage_root(), $_FILES['photo']);
	}

	$contact->save();
	Utils::redirectParent('./details.php?id=' . $contact->id());
}, $csrf_key);

$title = $contact->exists() ? 'Modifier un contact' : 'Nouveau contact';

$tpl->assign(compact('contact', 'csrf_key', 'title'));

$tpl->display(__DIR__ . '/../../templates/contacts/edit.tpl');
