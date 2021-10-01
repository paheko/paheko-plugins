<?php

namespace Garradin;

use Garradin\Plugin\Dompdf\PDF;

// Download and unzip DomPDF library
$url = 'https://github.com/dompdf/dompdf/releases/download/v1.0.2/dompdf_1-0-2.zip';

$file = tempnam(sys_get_temp_dir(), 'dompdf');

copy($url, $file);

$zip = new \ZipArchive;
$zip->open($file);
$zip->extractTo(PDF::DIRECTORY);
$zip->close();

unlink($file);

$plugin->registerSignal('pdf.stream', 'Garradin\Plugin\Dompdf\PDF::stream');
$plugin->registerSignal('pdf.create', 'Garradin\Plugin\Dompdf\PDF::create');
