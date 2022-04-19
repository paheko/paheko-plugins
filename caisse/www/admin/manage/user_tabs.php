<?php

namespace Garradin;

use Garradin\Plugin\Caisse\Tab;

require __DIR__ . '/_inc.php';

$tpl->assign('tabs', Tab::listForUser((string) qg('q')));

$tpl->display(PLUGIN_ROOT . '/templates/manage/user_tabs.tpl');
