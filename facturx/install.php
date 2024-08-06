<?php

namespace Paheko;

use Paheko\Plugin\Facturx\Facturx;
use Paheko\UserException;

$composer = json_decode(file_get_contents(__DIR__ . '/vendor/atgp/factur-x/composer.json'));

foreach ($composer->require as $name => $v) {
	if (substr($name, 0, 4) !== 'ext-') {
		continue;
	}

	$name = substr($name, 4);
	if (!extension_loaded($name)) {
		throw new UserException(sprintf('La librairie "%s" n\'est pas installée', $name));
	}
}

$plugin->registerSignal('facturx.create', Facturx::class . '::create');
