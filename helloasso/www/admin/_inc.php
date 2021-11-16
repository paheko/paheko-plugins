<?php

namespace Garradin;

use Garradin\Plugin\HelloAsso\HelloAsso;

$session->requireAccess($session::SECTION_USERS, $session::ACCESS_WRITE);

$ha = HelloAsso::getInstance();

if (!$ha->isConfigured()) {
	Utils::redirect(PLUGIN_URL . 'config_client.php');
}
