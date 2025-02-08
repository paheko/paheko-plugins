<?php

namespace Paheko;

use Paheko\Users\Session;

if ($plugin->needUpgrade()) {
	$plugin->upgrade();
}

$user_id = Session::getUserId();

if (!$user_id) {
	throw new UserException('Seuls les membres peuvent accéder à cette extension');
}

$vendor_root = SHARED_CACHE_ROOT . '/sabre';

// Install Sabre/DAV dependencies from source
if (!file_exists($vendor_root)) {
	$files = [
		'Sabre'         => 'https://github.com/sabre-io/dav/archive/refs/tags/4.7.0.zip',
		'Sabre_VObject' => 'https://github.com/sabre-io/vobject/archive/refs/tags/4.5.6.zip',
		'Sabre_Xml'     => 'https://github.com/sabre-io/xml/archive/refs/tags/4.0.6.zip',
		'Sabre_Uri'     => 'https://github.com/sabre-io/uri/archive/refs/tags/3.0.2.zip',
	];

	$root = SHARED_CACHE_ROOT . '/sabre';

	Utils::safe_mkdir($root);

	foreach ($files as $name => $url) {
		if (file_exists($root . '/' . $name)) {
			continue;
		}

		$path = $root . '/' . $name . '.zip';

		if (ini_get('allow_url_fopen')) {
			copy($url, $path);
		}
		elseif (!file_exists($path)) {
			echo 'Downloading files is forbidden by your server configuration (allow_url_fopen is disabled).<br />';
			printf('Please download this file: <a href="%s">%s</a><br />', $url);
			printf('And copy it here: <code>%s</code>', $path);
			exit;
		}

		$zip = new \PharData($path);
		$zip->extractTo($root . '/' . $name, null, true);
		unset($zip);

		$zip_root = glob($root . '/' . $name . '/*')[0];

		foreach (glob($zip_root . '/lib/*') as $file) {
			rename($file, $root . '/' . $name . '/' . basename($file));
		}

		Utils::deleteRecursive($zip_root, true);
		Utils::safe_unlink($path);
	}
}

spl_autoload_register(function (string $name) use ($vendor_root) {
	$path = $vendor_root . '/' . str_replace('\\', '/', $name);
	$path = str_replace('Sabre/VObject', 'Sabre_VObject', $path);
	$path = str_replace('Sabre/Xml', 'Sabre_Xml', $path);
	$path = str_replace('Sabre/Uri', 'Sabre_Uri', $path);
	$path .= '.php';

	if (file_exists($path)) {
		require_once $path;
	}
	else {
		die($path);
	}
});
