<?php

namespace Paheko;

use Paheko\Entities\Module;

$plugin->unregisterSignal('cron');

$ext = Extensions::get('helloasso_checkout_snippets');
if($ext) $ext->disable();
@unlink(ROOT . DIRECTORY_SEPARATOR . Module::ROOT . DIRECTORY_SEPARATOR . 'helloasso_checkout_snippets');