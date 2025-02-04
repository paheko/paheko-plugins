<?php

namespace Paheko;

$plugin->unregisterSignal('cron');

$ext = Extensions::get('helloasso_checkout_snippets');
if($ext) $ext->disable();

Utils::deleteRecursive(ROOT . '/modules/helloasso_checkout_snippets');