<?php

namespace Paheko\Plugin\PIM;

use Paheko\UserException;
use Paheko\Plugin\PIM\Entities\Event_Category;
use Paheko\Users\Session;

require __DIR__ . '/../_inc.php';

$events = new Events(Session::getUserId());

if ($id = intval($_GET['id'] ?? 0)) {
	$cat = $events->getCategory($id);

	if (!$cat) {
		throw new UserException('Catégorie inconnue');
	}
}
else {
	$cat = new Event_Category;
	$cat->id_user = $user_id;
}

$csrf_key = 'pim_category_edit';

$form->runIf('save', function () use ($cat) {
	$cat->importForm();
	$cat->save();
}, $csrf_key, './');

$title = $cat->exists() ? 'Modifier une catégorie' : 'Nouvelle catégorie';

$tpl->assign(compact('cat', 'csrf_key', 'title'));

$tpl->display(__DIR__ . '/../../templates/categories/edit.tpl');
