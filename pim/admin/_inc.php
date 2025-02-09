<?php

namespace Paheko\Plugin\PIM;

if ($plugin->needUpgrade()) {
	$plugin->upgrade();
}

PIM::verifyAccess();
