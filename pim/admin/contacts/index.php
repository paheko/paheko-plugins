<?php

namespace Paheko\Plugin\PIM;

use Paheko\Users\Session;

require __DIR__ . '/../_inc.php';

$contacts = new Contacts(Session::getUserId());

$archived = isset($_GET['archived']);
$list = $contacts->getList($archived);
$list->loadFromQueryString();

$tpl->assign(compact('archived', 'list'));
$tpl->display(__DIR__ . '/../../templates/contacts/index.tpl');
