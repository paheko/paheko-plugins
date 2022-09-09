<?php

namespace Garradin;
use Garradin\Plugin\Caisse\Categories;

require __DIR__ . '/../_inc.php';

$tpl->assign('list', Categories::list());

$tpl->display(PLUGIN_ROOT . '/templates/manage/categories/index.tpl');
