<?php

namespace Paheko\Plugin\PIM;

require __DIR__ . '/../_inc.php';

$events = new Events($user_id);
$list = $events->listCategories();

$tpl->assign(compact('list'));

$tpl->display(__DIR__ . '/../../templates/categories/index.tpl');
