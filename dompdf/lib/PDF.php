<?php

namespace Paheko\Plugin\Dompdf;

use Paheko\Utils;

use Dompdf\Dompdf;
use const Paheko\{SHARED_CACHE_ROOT, CACHE_ROOT, WWW_URL, ADMIN_URL};

use Paheko\Entities\Signal;

class PDF
{
	const VERSION = '3.0.0';
	const URL = 'https://github.com/dompdf/dompdf/releases/download/v' . self::VERSION . '/dompdf-' . self::VERSION . '.zip';
	const DIRECTORY = SHARED_CACHE_ROOT . '/dompdf';
	const VERSION_FILE = self::DIRECTORY . '/dompdf/VERSION';
	const LOADER = self::DIRECTORY . '/dompdf/vendor/autoload.php';

	static public function remove(): void
	{
		Utils::deleteRecursive(self::DIRECTORY, true);
	}

	static public function install(): void
	{
		if (!function_exists('imagecreatefromwebp')) {
			throw new \LogicException('You need to install the PHP GD extension to be able to use this extension.');
		}

		// Remove old setup, new one is shared between setups
		if (file_exists(CACHE_ROOT . '/dompdf')) {
			Utils::deleteRecursive(CACHE_ROOT . '/dompdf', true);
		}

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

		$file = SHARED_CACHE_ROOT . '/dompdf.zip';

		if (ini_get('allow_url_fopen')) {
			copy(self::URL, $file);
		}
		elseif (!file_exists($file)) {
			echo 'Downloading files is forbidden by your server configuration (allow_url_fopen is disabled).<br />';
			printf('Please download this file: <a href="%s">%s</a><br />', self::URL);
			printf('And copy it here: <code>%s</code>', $file);
			exit;
		}

		$zip = new \PharData($file);
		$zip->extractTo(self::DIRECTORY, null, true);
		unset($zip);

		Utils::safe_unlink($file);
	}

	static protected function DomPDF(): Dompdf
	{
		$file = self::LOADER;

		if (!file_exists($file)) {
			self::install();
		}

		require_once self::LOADER;

		// instantiate and use the dompdf class
		$dompdf = new Dompdf;
		$host1 = parse_url(WWW_URL, PHP_URL_HOST);
		$host2 = parse_url(ADMIN_URL, PHP_URL_HOST);

		$options = $dompdf->getOptions();

		// Set chroot for file:// protocol, just in case
		$options->setChroot(CACHE_ROOT);

		// Only alow http/https protocols
		$options->setAllowedProtocols(['http://', 'https://']);

		// see https://github.com/dompdf/dompdf/pull/3377
		if (method_exists($options, 'setAllowedRemoteHosts')) {
			$options->setAllowedRemoteHosts([$host1, $host2]);
		}

		// Allow remote requests
		$options->set('isRemoteEnabled', true);

		$options->set('defaultMediaType', 'print');
		$options->set('isJavascriptEnabled', false);

		return $dompdf;
	}

	static protected function render(string $html)
	{
		$dompdf = self::DomPDF();

		// Detect landscape output
		// see https://github.com/dompdf/dompdf/issues/3562
		if (strpos($html, 'data-prefer-landscape') !== false) {
			$dompdf->setPaper('A4', 'landscape');
		}

		$dompdf->loadHtml($html);

		// Render the HTML as PDF
		$dompdf->render();
		return $dompdf;
	}

	static public function create(Signal $signal): void
	{

		$html = file_get_contents($signal->getIn('source'));

		$dompdf = self::render($html);

		file_put_contents($signal->getIn('target'), $dompdf->output());
		$signal->stop();

	}

	static public function stream(Signal $signal): void
	{
		$dompdf = self::render($signal->getIn('string'));

		echo $dompdf->output();

		$signal->stop();
	}
}