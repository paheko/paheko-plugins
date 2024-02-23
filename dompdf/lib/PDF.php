<?php

namespace Paheko\Plugin\Dompdf;

use Paheko\Utils;

use Dompdf\Dompdf;
use const Paheko\{SHARED_CACHE_ROOT, CACHE_ROOT, WWW_URL, ADMIN_URL};

use Paheko\Entities\Signal;

class PDF
{
	const VERSION = '2.0.4';
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

		copy(self::URL, $file);

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
		$options->setChroot(self::DIRECTORY);

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

	static public function create(Signal $signal): void
	{
		$dompdf = self::DomPDF();

		$dompdf->loadHtmlFile($signal->getIn('source'));

		// Render the HTML as PDF
		$dompdf->render();

		file_put_contents($signal->getIn('target'), $dompdf->output());
		$signal->stop();

	}

	static public function stream(Signal $signal): void
	{
		$dompdf = self::DomPDF();

		$dompdf->loadHtml($signal->getIn('string'));

		// Render the HTML as PDF
		$dompdf->render();

		echo $dompdf->output();

		$signal->stop();
	}
}