<?php

namespace Paheko\Plugin\PIM;

require_once __DIR__ . '/../_inc.php';

$c = new Contacts($user_id);

$list = $c->listAll(false);

$tpl->assign(compact('list'));

$tpl->PDF(__DIR__ . '/../../templates/contacts/print.tpl', 'Contacts');
