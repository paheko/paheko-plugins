<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso\HelloAsso;

$ha = HelloAsso::getInstance();

if (!$ha->isConfigured()) {
	Utils::redirect('./config_client.php');
}
