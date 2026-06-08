<?php

namespace Paheko;

$tpl->assign('has_java', Utils::quick_exec('which java'));

$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
