<?php

namespace Paheko\Plugin\PIM;

if ($plugin->needUpgrade()) {
	$plugin->upgrade();
}

PIM::verifyAccess();

spl_autoload_register(function (string $name) {
	if (strpos($name, 'Sabre') !== false) {
		PIM::enableDependencies();
	}
});
