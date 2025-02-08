<?php

namespace Paheko\Plugin\PIM;

use Paheko\UserException;

require __DIR__ . '/../_inc.php';

$events = new Events($user_id);

$id = intval($_GET['id'] ?? 0);

$cat = $events->getCategory($id);

if (!$cat) {
	throw new UserException('Catégorie introuvable', 404);
}

$csrf_key = 'pim_event_cat_delete';

$form->runIf('delete', function () use ($cat) {
	if (empty($_POST['confirm_delete'])) {
		throw new UserException('Merci de cocher la case pour confirmer la suppression.');
	}

	$cat->delete();
}, $csrf_key, './');

$tpl->assign(compact('cat', 'csrf_key'));

$tpl->display(__DIR__ . '/../../templates/categories/delete.tpl');
