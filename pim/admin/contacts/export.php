<?php

namespace Paheko\Plugin\PIM;
use Paheko\Users\Session;

require __DIR__ . '/../_inc.php';

$contacts = new Contacts(Session::getUserId());

$contacts->exportAll();
