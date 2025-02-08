<?php

namespace Paheko\Plugin\PIM;

use Paheko\UserException;
use Paheko\Plugin\PIM\Entities\Contact;

require __DIR__ . '/../_inc.php';

$contacts = new Contacts($user_id);

$id = intval($_GET['id'] ?? 0);
$contact = $contacts->get($id);

if (!$contact) {
	throw new UserException('Contact inconnu');
}

$title = $contact->getFullName();

$tpl->assign(compact('contact', 'title'));

$tpl->display(__DIR__ . '/../../templates/contacts/details.tpl');
