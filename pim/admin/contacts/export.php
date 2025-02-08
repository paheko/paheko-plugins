<?php

namespace Paheko\Plugin\PIM;

require_once __DIR__ . '/../_inc.php';

$c = new Contacts($user_id);

$c->exportAll();
