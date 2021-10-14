<?php

namespace Garradin;

use Garradin\Plugin\HelloAsso\HelloAsso;

$session->requireAccess($session::SECTION_USERS, $session::ACCESS_ADMIN);

$ha = HelloAsso::getInstance();

$list = $ha->listForms();
$tpl->assign('list', $list);

$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
