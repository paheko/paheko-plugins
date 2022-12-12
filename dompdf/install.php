<?php

namespace Garradin;

use Garradin\Plugin\Dompdf\PDF;

if (!class_exists('ZipArchive')) {
	throw new UserException('Cette extension nécessite l\'installation du module PHP zip (apt install php-zip).');
}

// Download and unzip DomPDF library
$url = 'https://github.com/dompdf/dompdf/releases/download/v2.0.1/dompdf-2.0.1.zip';

$file = tempnam(CACHE_ROOT, 'dompdf');

copy($url, $file);

$zip = new \ZipArchive;
$zip->open($file);
$zip->extractTo(PDF::DIRECTORY);
$zip->close();

unlink($file);

$plugin->registerSignal('pdf.stream', 'Garradin\Plugin\Dompdf\PDF::stream');
$plugin->registerSignal('pdf.create', 'Garradin\Plugin\Dompdf\PDF::create');
