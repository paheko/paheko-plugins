<?php

namespace Paheko;

use Paheko\Plugin\Stock_Velos\Velos;

$velos = new Velos($plugin);

$tpl->assign('plugin_css', ['style.css']);
$tpl->assign('fields', $velos->getFields());
