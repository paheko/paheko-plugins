<?php

namespace Garradin;

use KD2\HTTP;
use Garradin\Plugin\HelloAsso\HelloAsso;

$session->requireAccess($session::SECTION_USERS, $session::ACCESS_ADMIN);

$ha = HelloAsso::getInstance();

if (!$ha->getOAuth()) {
	Utils::redirect(PLUGIN_URL . 'config_client.php');
}

$tpl->assign('list', $ha->listForms());
$tpl->assign('restricted', $ha->isTrial());

$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
