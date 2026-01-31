<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Categories;
use Paheko\Plugin\Caisse\Products;
use Paheko\Services\Services;

require __DIR__ . '/../_inc.php';

if (qg('new') !== null) {
	$product = Products::new();
	$csrf_key = 'product_new';
}
else {
	$product = Products::get((int) qg('id'));

	if (!$product) {
		throw new UserException('Ce produit n\'existe pas');
	}

	$csrf_key = 'product_edit_' . $product->id();
}

$tpl->assign(compact('product', 'csrf_key'));

if (qg('delete') !== null) {
	$form->runIf('delete', function () use ($product) {
		if (!f('confirm_delete')) {
			throw new UserException('Merci de cocher la case pour confirmer la suppression.');
		}

		$product->delete();
	}, $csrf_key, './');

	$tpl->display(PLUGIN_ROOT . '/templates/manage/products/delete.tpl');
}
else {
	$form->runIf('save', function () use ($product) {
		$product->importForm();
		$product->save();
		$product->setMethods(array_keys(f('methods') ?? []));
		$product->setLinkedProducts(array_keys(f('linked_products') ?? []));
	}, $csrf_key, './');

	$methods = $product->listPaymentMethods();
	$categories = Categories::listAssoc();
	$fees = Services::listGroupedWithFeesForSelect(false);
	$linked_products = $product->listLinkedProductsAssoc();

	$tpl->assign(compact('methods', 'categories', 'fees', 'linked_products'));

	$tpl->display(PLUGIN_ROOT . '/templates/manage/products/edit.tpl');
}