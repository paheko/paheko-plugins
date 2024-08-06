<?php

namespace Paheko\Plugin\Facturx;

use Paheko\Entities\Signal;
use Paheko\Utils;

class Facturx
{
	static public function create(Signal $signal): Signal
	{
		require_once __DIR__ . '/../vendor/autoload.php';

		$xml = $signal->getIn('xml');
		$pdf = file_get_contents(Utils::filePDF($signal->getIn('html')));

		$writer = new \Atgp\FacturX\Writer;
		$signal->setOut('pdf', $writer->generate($pdf, $xml));
		$signal->stop();
		return $signal;
	}
}
