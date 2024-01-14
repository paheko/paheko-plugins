<?php

namespace Paheko;

$tpl->assign('has_java', exec('which java'));

$tpl->display(PLUGIN_ROOT . '/templates/index.tpl');
