<?php

namespace Paheko;

use Paheko\Plugin\Dompdf\PDF;

// Download and unzip DomPDF library
PDF::install();

$plugin->registerSignal('pdf.stream', 'Paheko\Plugin\Dompdf\PDF::stream');
$plugin->registerSignal('pdf.create', 'Paheko\Plugin\Dompdf\PDF::create');
