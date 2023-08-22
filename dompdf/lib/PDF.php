<?php

namespace Paheko\Plugin\Dompdf;

use Dompdf\Dompdf;
use const Paheko\CACHE_ROOT;

use Paheko\Entities\Signal;

class PDF
{
	const DIRECTORY = CACHE_ROOT . '/dompdf';

	static protected function DomPDF(): Dompdf
	{
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