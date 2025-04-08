<?php

namespace Paheko;

use Paheko\Files\Files;
use Paheko\Entities\Files\File;
use Paheko\UserTemplate\UserTemplate;

use Paheko\Plugin\Caisse\Categories;
use Paheko\Plugin\Caisse\Products;

require __DIR__ . '/../_inc.php';

$csrf_key = 'print';

$form->runIf('print', function () use ($tpl) {
	$products = Products::listByCategory();
	$selected = (array)f('selected');

	foreach ($products as $key => $product) {
		// Exclude archived products and from categories that should not be printed
		if (!in_array($product->category, $selected)
			|| $product->archived) {
			unset($products[$key]);
		}
	}

	$tpl->assign('products', $products);

	$out = $tpl->fetch(PLUGIN_ROOT . '/templates/manage/products/print.tpl');
	$filename = 'Produits.pdf';

	header('Content-type: application/pdf');
	header(sprintf('Content-Disposition: attachment; filename="%s"', Utils::safeFileName($filename)));
	Utils::streamPDF($out);
	exit;
}, $csrf_key);

$tpl->assign('categories', Categories::listAssoc());
$tpl->assign(compact('csrf_key'));

$tpl->display(PLUGIN_ROOT . '/templates/manage/products/print_select.tpl');
