<?php

namespace Paheko\Plugin\Snappymail;

use Paheko\Utils;

use const Paheko\{SHARED_CACHE_ROOT, STATIC_CACHE_ROOT, WWW_URL, ADMIN_URL};

use Paheko\Entities\Signal;

class Snappymail
{
	const DATA_ROOT = STATIC_CACHE_ROOT . '/snappymail';

	const VERSION = '2.36.3';
	const URL = 'https://github.com/the-djmaze/snappymail/releases/download/v' . self::VERSION . '/snappymail-' . self::VERSION . '.zip';
	const DIRECTORY = SHARED_CACHE_ROOT . '/snappymail';
	const VERSION_FILE = self::DIRECTORY . '/snappymail/data/VERSION';
	const PASSWORD_FILE = self::DIRECTORY . '/snappymail/data/_data_/_default_/admin_password.txt';
	const LOADER = self::DIRECTORY . '/index.php';

	static public function remove(): void
	{
		Utils::deleteRecursive(self::DIRECTORY, true);
	}

	static public function install(): void
	{
		if (file_exists(self::VERSION_FILE)) {
			$installed_version = trim(file_get_contents(self::VERSION_FILE));

			// Assume that a non-numeric version string is from Git
			if (!ctype_digit(substr($installed_version, 0, 1))) {
				return;
			}

			if ($installed_version === self::VERSION) {
				return;
			}

			self::remove();
		}

		$file = SHARED_CACHE_ROOT . '/snappymail.zip';

		copy(self::URL, $file);

		$zip = new \PharData($file);
		$zip->extractTo(self::DIRECTORY, null, true);
		unset($zip);

		Utils::safe_unlink($file);

		// Disable admin panel
		@mkdir(dirname(self::PASSWORD_FILE), fileperms(SHARED_CACHE_ROOT), true);
		file_put_contents(self::PASSWORD_FILE, sha1(random_bytes(16)));
	}

	static public function route(): void
	{
		global $sAppPath;
		$file = self::LOADER;

		if (!file_exists($file)) {
			self::install();
		}

		define('APP_DATA_FOLDER_PATH', self::DATA_ROOT);

		$_ENV['SNAPPYMAIL_INCLUDE_AS_API'] = true;

		if ($url = ($_GET['url'] ?? null)) {
			if (false !== strpos($url, '..')) {
				throw new UserException('Invalid address', 403);
			}

			if (!file_exists(self::DIRECTORY . $url)) {
				throw new UserException('Invalid address', 404);
			}

			$ext = substr($url, strrpos($url, '.')+1);

			$types = [
				'js' => 'text/javascript',
				'css' => 'text/css',
				'ttf' => 'font/truetype',
				'otf' => 'font/opentype',
				'eot' => 'application/vnd.ms-fontobject',
				'png' => 'image/png',
				'svg' => 'image/svg+xml',
				'woff' => 'font/woff',
				'woff2' => 'font/woff2',
			];

			if (array_key_exists($ext, $types)) {
				header('Content-Type: ' . $types[$ext]);
				readfile(self::DIRECTORY . $url);
				return;
			}

			if (substr($url, -4) !== '.php') {
				die($url);
			}

			die('fail');
		}

		require_once self::LOADER;

		$oConfig = \RainLoop\Api::Config();
		$oConfig->Set('webmail', 'app_path', ADMIN_URL . 'p/snappymail/?url=');
		//$oConfig->Set('webmail', 'allow_languages_on_settings', empty($_POST['snappymail-nc-lang']));
		//$oConfig->Set('login', 'allow_languages_on_login', empty($_POST['snappymail-nc-lang']));
		$oConfig->Save();

		\RainLoop\Service::Handle();
	}
}