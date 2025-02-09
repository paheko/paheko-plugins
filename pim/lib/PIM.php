<?php

namespace Paheko\Plugin\PIM;

use Paheko\Users\Session;
use Paheko\Utils;

use const Paheko\SHARED_CACHE_ROOT;

class PIM
{
	const VENDOR_ROOT = SHARED_CACHE_ROOT . '/sabre';

	static public function verifyAccess(Session $session = null): void
	{
		$session ??= Session::getInstance();

		if (!$session->isLogged()) {
			throw new UserException('Seuls les membres connectés peuvent accéder à cette extension', 403);
		}

		$user_id = $session->user()->id;

		if (!$user_id) {
			throw new UserException('Seuls les membres peuvent accéder à cette extension', 403);
		}
	}

	static public function enableDependencies(): void
	{
		static $enabled = false;

		if ($enabled) {
			return;
		}

		self::installDependencies();

		require self::VENDOR_ROOT . '/Sabre_Uri/functions.php';
		require self::VENDOR_ROOT . '/Sabre_Http/functions.php';
		require self::VENDOR_ROOT . '/Sabre_Xml/Serializer/functions.php';
		require self::VENDOR_ROOT . '/Sabre_Xml/Deserializer/functions.php';

		spl_autoload_register(function (string $name) {
			$path = self::VENDOR_ROOT . '/' . str_replace('\\', '/', $name);
			$path = str_replace('Sabre/VObject', 'Sabre_VObject', $path);
			$path = str_replace('Sabre/HTTP', 'Sabre_Http', $path);
			$path = str_replace('Sabre/Xml', 'Sabre_Xml', $path);
			$path = str_replace('Sabre/Uri', 'Sabre_Uri', $path);
			$path = str_replace('Sabre/Event', 'Sabre_Event', $path);
			$path = str_replace('Psr/Log', 'Psr_Log', $path);
			$path .= '.php';

			if (file_exists($path)) {
				require $path;
			}
			else {
				//die($path);
			}
		});

		$enabled = true;
	}

	static public function installDependencies(): void
	{
		if (file_exists(self::VENDOR_ROOT)) {
			return;
		}

		// Install Sabre/DAV dependencies from source
		$files = [
			'Sabre'         => 'https://github.com/sabre-io/dav/archive/refs/tags/4.7.0.zip',
			'Sabre_VObject' => 'https://github.com/sabre-io/vobject/archive/refs/tags/4.5.6.zip',
			'Sabre_Xml'     => 'https://github.com/sabre-io/xml/archive/refs/tags/2.2.11.zip',
			'Sabre_Uri'     => 'https://github.com/sabre-io/uri/archive/refs/tags/2.3.4.zip',
			'Sabre_Event'   => 'https://github.com/sabre-io/event/archive/refs/tags/5.1.7.zip',
			'Sabre_Http'    => 'https://github.com/sabre-io/http/archive/refs/tags/5.1.12.zip',
			'Psr_Log'       => 'https://github.com/php-fig/log/archive/refs/tags/3.0.2.zip',
		];

		$root = self::VENDOR_ROOT;

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

			foreach (glob($zip_root . '/src/*') as $file) {
				rename($file, $root . '/' . $name . '/' . basename($file));
			}

			Utils::deleteRecursive($zip_root, true);
			Utils::safe_unlink($path);
		}
	}
}
