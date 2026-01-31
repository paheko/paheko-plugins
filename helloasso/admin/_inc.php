<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\HelloAsso;

$session->requireAccess($session::SECTION_USERS, $session::ACCESS_WRITE);

$ha = HelloAsso::getInstance();

if (!$ha->isConfigured()) {
	Utils::redirect('./config_client.php');
}
