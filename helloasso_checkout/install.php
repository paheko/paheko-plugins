<?php

namespace Paheko;

use Paheko\Plugin\HelloAsso_Checkout\API;
use Paheko\Entities\Module;

$plugin->registerSignal('cron', [API::class, 'refreshTokenIfExipired']);

@unlink(ROOT . DIRECTORY_SEPARATOR . Module::ROOT . DIRECTORY_SEPARATOR . 'helloasso_checkout_snippets');
@symlink($plugin->path('module'), ROOT . DIRECTORY_SEPARATOR . Module::ROOT . DIRECTORY_SEPARATOR . 'helloasso_checkout_snippets');

$ext = Extensions::get('helloasso_checkout_snippets');
if($ext) $ext->enable();