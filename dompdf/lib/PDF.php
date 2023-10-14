<?php

namespace Paheko\Plugin\Dompdf;

use Dompdf\Dompdf;
use const Paheko\CACHE_ROOT;

use Paheko\Entities\Signal;

class PDF
{
	const URL = 'https://github.com/dompdf/dompdf/releases/download/v2.0.3/dompdf_2-0-3.zip';
	const DIRECTORY = CACHE_ROOT . '/dompdf';

	static public function install(): void
	{
		$file = CACHE_ROOT . '/dompdf.zip';

		copy(self::URL, $file);

		$zip = new \PharData($file);
		$zip->extractTo(self::DIRECTORY, null, true);
		unset($zip);

		unlink($file);
	}

	static protected function DomPDF(): Dompdf
	{
		$file = self::DIRECTORY . '/dompdf/autoload.inc.php';

		if (!file_exists($file)) {
			self::install();
		}

		require_once self::DIRECTORY . '/dompdf/autoload.inc.php';

		// instantiate and use the dompdf class
		$dompdf = new Dompdf;

		$options = $dompdf->getOptions();
		$options->setChroot(CACHE_ROOT);
		$options->set('isRemoteEnabled', true);
		$options->set('defaultMediaType', 'print');
		$options->set('isJavascriptEnabled', false);

		return $dompdf;
	}

	static public function create(Signal $signal): void
	{
		$dompdf = self::DomPDF();

		$dompdf->loadHtmlFile($signal->getIn('source'));

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		file_put_contents($signal->getIn('target'), $dompdf->output());
		$signal->stop();

	}

	static public function stream(Signal $signal): void
	{
		$dompdf = self::DomPDF();

		$dompdf->loadHtml($signal->getIn('string'));

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		echo $dompdf->output();

		$signal->stop();
	}
}