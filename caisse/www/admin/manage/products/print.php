<?php

namespace Garradin;

use Garradin\Files\Files;
use Garradin\Entities\Files\File;
use Garradin\UserTemplate\UserTemplate;

use Garradin\Plugin\Caisse\Product;

require __DIR__ . '/../_inc.php';

$products = Product::listByCategory();

$tpl->assign('products_categories', $products);

$out = $tpl->fetch(PLUGIN_ROOT . '/templates/manage/products/print.tpl');
$filename = 'Produits.pdf';

header('Content-type: application/pdf');
header(sprintf('Content-Disposition: attachment; filename="%s"', Utils::safeFileName($filename)));
Utils::streamPDF($out);
