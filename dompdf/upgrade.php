<?php

namespace Garradin;

use Garradin\Plugin\Dompdf\PDF;

// Delete DOMPDF library
Utils::deleteRecursive(PDF::DIRECTORY);

// Re-install
require __DIR__ . '/install.php';
