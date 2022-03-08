<?php

namespace Garradin;

use Garradin\Plugin\Dompdf\PDF;

// Download and unzip DomPDF library
$url = 'https://github.com/dompdf/dompdf/releases/download/v1.2.0/dompdf_1-2-0.zip';

$file = tempnam(CACHE_ROOT, 'dompdf');

copy($url, $file);

$zip = new \ZipArchive;
$zip->open($file);
$zip->extractTo(PDF::DIRECTORY);
$zip->close();

unlink($file);

$plugin->registerSignal('pdf.stream', 'Garradin\Plugin\Dompdf\PDF::stream');
$plugin->registerSignal('pdf.create', 'Garradin\Plugin\Dompdf\PDF::create');
