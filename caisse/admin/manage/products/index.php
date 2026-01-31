<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Products;

require __DIR__ . '/../_inc.php';

$csrf_key = 'products';
$archived = !empty($_GET['archived']);
$search = $_GET['q'] ?? null;
$action = $_POST['action'] ?? null;
$selected = null;

if (isset($_POST['selected']) && is_array($_POST['selected'])) {
	$selected = array_map('intval', $_POST['selected']);
}

$form->runIf($action === 'archive', function () use ($archived, $selected) {
	Products::markSelectedAsArchived($selected, !$archived);
}, $csrf_key, Utils::getSelfURI());

$form->runIf('confirm_delete', function () use ($selected) {
	Products::deleteSelected($selected);
}, $csrf_key, Utils::getSelfURI());

if ($action === 'delete') {
	$tpl->assign('extra', compact('selected'));
	$tpl->assign('count', count($selected));
	$tpl->assign(compact('csrf_key'));
	$tpl->display(PLUGIN_ROOT . '/templates/manage/products/delete_selected.tpl');
}
else {
	$list = Products::getList($archived, $search);
	$list->loadFromQueryString();

	$tpl->assign(compact('list', 'archived', 'search', 'csrf_key'));

	$tpl->display(PLUGIN_ROOT . '/templates/manage/products/index.tpl');
}
