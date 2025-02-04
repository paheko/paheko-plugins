<?php

namespace Paheko;

$plugin->unregisterSignal('cron');

$ext = Extensions::get('helloasso_checkout_snippets');
$ext->disable();

Utils::deleteRecursive(ROOT . '/modules/helloasso_checkout_snippets');