<?php

namespace Garradin;

use Garradin\Plugin\Caisse\POS;

$db = DB::getInstance();

$old_version = $plugin->getInfos('version');

if (version_compare($old_version, '0.2', '<')) {
	$plugin->setConfig('diff_email', null);
}
