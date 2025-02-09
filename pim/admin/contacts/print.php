<?php

namespace Paheko\Plugin\PIM;

use Paheko\Users\Session;

require __DIR__ . '/../_inc.php';

$contacts = new Contacts(Session::getUserId());

$list = $contacts->listAll(false);

$tpl->assign(compact('list'));

$tpl->PDF(__DIR__ . '/../../templates/contacts/print.tpl', 'Contacts');
