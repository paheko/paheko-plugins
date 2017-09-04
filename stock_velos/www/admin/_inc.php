<?php

namespace Garradin;

use Garradin\Plugin\Stock_Velos\Velos;

$session->requireAccess('membres', Membres::DROIT_ECRITURE);

$velos = new Velos;

$tpl->assign('plugin_css', ['style.css']);
$tpl->assign('plugin_tpl', PLUGIN_ROOT . '/templates/');
