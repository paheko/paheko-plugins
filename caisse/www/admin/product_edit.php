<?php

namespace Garradin;
use Garradin\Plugin\Caisse\Product;

require __DIR__ . '/_inc.php';

if (qg('new') !== null) {
	$product = Product::new();
	$csrf_key = 'product_new';
}
else {
	$product = Product::get((int) qg('id'));
	$csrf_key = 'product_edit_' . $product->id();
}

$form->runIf('save', function () use ($product) {
	$product->importForm();
	$product->save();
	$product->setMethods(array_keys(f('methods') ?? []));
}, $csrf_key, 'products.php');

$methods = $product->listPaymentMethods();
$categories = Product::listCategoriesAssoc();

$tpl->assign(compact('product', 'csrf_key', 'methods', 'categories'));

$tpl->display(PLUGIN_ROOT . '/templates/product_edit.tpl');
