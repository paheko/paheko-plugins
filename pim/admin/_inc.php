<?php

use Paheko\Users\Session;

if ($plugin->needUpgrade()) {
	$plugin->upgrade();
}

$user_id = Session::getUserId();

if (!$user_id) {
	throw new UserException('Seuls les membres peuvent accéder à cette extension');
}
