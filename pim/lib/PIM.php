<?php

namespace Paheko\Plugin\PIM;

use Paheko\Entities\Plugin;
use Paheko\Entities\Users\User;
use Paheko\Users\Session;
use Paheko\DB;
use Paheko\UserException;
use Paheko\Utils;
use Paheko\Users\Users;

use const Paheko\SHARED_CACHE_ROOT;

class PIM
{
	const VENDOR_ROOT = SHARED_CACHE_ROOT . '/sabre';

	protected User $user;

	public function __construct(User $user)
	{
		$this->user = $user;
	}

	public function getDAVCredentials(Plugin $plugin): ?array
	{
		if (DB::getInstance()->test('users_app_passwords', 'id_user = ? AND id_plugin = ?', $this->user->id(), $plugin->id())) {
			return [
				'login' => $this->user->id(),
				'url'   => $plugin->url(),
			];
		}
		else {
			return null;
		}
	}

	public function generateDAVCredentials(Plugin $plugin): array
	{
		$db = DB::getInstance();
		// Only keep one credential for this plugin and user
		$db->delete('users_app_passwords', 'id_user = ? AND id_plugin = ?', $this->user->id(), $plugin->id());

		$password = $this->user->createAppPassword('Agenda et contacts (accès CardDAV/CalDAV)', $plugin->id());
		$id = strtok($password, '.');
		$password = strtok('');

		return [
			'login'    => $this->user->id(),
			'password' => $password,
			'url'      => $plugin->url(),
		];
	}

	static public function login(Plugin $plugin, string $login, string $password): ?User
	{
		$db = DB::getInstance();

		// We can't use useAppPassword directly, as we used to not ask
		$id = $db->firstColumn('SELECT id FROM users_app_passwords WHERE id_user = ? AND id_plugin = ?;', (int) $login, $plugin->id());

		if (!$id) {
			return null;
		}

		$user = Users::get((int)$login);

		if (!$user->useAppPassword($id . '.' . $password)) {
			return null;
		}

		return $user;
	}

	static public function verifyAccess(?Session $session = null): void
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
		// wget -O - URL | sha256sum
		$files = [
			'Sabre' => [
				'url' => 'https://github.com/sabre-io/dav/archive/refs/tags/4.7.0.zip',
				'hash' => 'dff6752b536516aece899a38c83e066cbc551a806b3887bb737a570be97088e8',
			],
			'Sabre_VObject' => [
				'url' => 'https://github.com/sabre-io/vobject/archive/refs/tags/4.5.6.zip',
				'hash' => 'e9415f60870a43e2d0be511d1b292a674afc0b3bc6035284baa583e5a8692591',
			],
			'Sabre_Xml' => [
				'url' => 'https://github.com/sabre-io/xml/archive/refs/tags/2.2.11.zip',
				'hash' => '36e41073b1e587651ecb2027edb3198becc1856b49a620a2429339e90b4b97ed',
			],
			'Sabre_Uri' => [
				'url' => 'https://github.com/sabre-io/uri/archive/refs/tags/2.3.4.zip',
				'hash' => '724a0a98cd7ebc4435e9e7a8df08eca483c8b91aa5405374bc6ecae73c0e3ab3',
			],
			'Sabre_Event' => [
				'url' => 'https://github.com/sabre-io/event/archive/refs/tags/5.1.7.zip',
				'hash' => 'eeaecc640157871d07625d2896b7d52d868f7033aa7e21874f6ef9988dc6e53b',
			],
			'Sabre_Http' => [
				'url' => 'https://github.com/sabre-io/http/archive/refs/tags/5.1.12.zip',
				'hash' => '45bc147ec2c570eed43364994a37ac784001150934e784e3d36ab9b81c20b32f',
			],
			'Psr_Log' => [
				'url' => 'https://github.com/php-fig/log/archive/refs/tags/3.0.2.zip',
				'hash' => 'c365225b9567800008110f8f7b2873bed86c5a0c40ce3ae399059ff3c22b778e',
			],
		];

		$root = self::VENDOR_ROOT;

		Utils::safe_mkdir($root);

		foreach ($files as $name => $pkg) {
			if (file_exists($root . '/' . $name)) {
				continue;
			}

			$path = $root . '/' . $name . '.zip';

			if (ini_get('allow_url_fopen')) {
				copy($pkg['url'], $path);
			}
			elseif (!file_exists($path)) {
				echo 'Downloading files is forbidden by your server configuration (allow_url_fopen is disabled).<br />';
				printf('Please download this file: <a href="%s">%s</a><br />', $pkg['url']);
				printf('And copy it here: <code>%s</code>', $path);
				exit;
			}

			$hash = hash_file('sha256', $path);

			if (!hash_equals($pkg['hash'], $hash)) {
				throw new \LogicException(sprintf('Downloaded package "%s" does not match hash: %s', $pkg['url'], $pkg['hash']));
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
