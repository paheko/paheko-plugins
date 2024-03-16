<?php

namespace Paheko\Plugin\Taima;

use Paheko\Plugin\Taima\Tracking;
use Paheko\Plugin\Taima\Entities\Entry;
use Paheko\Users\Users;
use Paheko\Utils;
use Paheko\UserException;

use function Paheko\{f, qg};

$session->requireAccess($session::SECTION_USERS, $session::ACCESS_ADMIN);

require_once __DIR__ . '/_inc.php';

$list = null;
$filters = [];
$title = 'Suivi';
$subtitle = null;

if ($id = (int)($_GET['id_user'] ?? 0)) {
	$filters['user_id'] = $id;
	$subtitle = sprintf('Membre : %s', Users::getName($id));
}
elseif (($_GET['except_me'] ?? null) !== null) {
	$filters['except'] = $session::getUserId();
}
elseif ($id = (int)($_GET['id_task'] ?? 0)) {
	$filters['task_id'] = $id;
	$subtitle = sprintf('Catégorie : %s', Tracking::getTaskLabel($id));
}

$list = Tracking::getList($filters);
$list->loadFromQueryString();

$tpl->assign(compact('list', 'filters', 'title', 'subtitle'));

$tpl->display(\Paheko\PLUGIN_ROOT . '/templates/all.tpl');
