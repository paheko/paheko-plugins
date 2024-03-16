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
	$filters['id_user'] = $id;
	$subtitle = sprintf('Membre : %s', Users::getName($id));
}
elseif (($_GET['except'] ?? null) !== null) {
	$filters['except'] = $session::getUserId();
}
elseif ($id = (int)($_GET['id_task'] ?? 0)) {
	$filters['id_task'] = $id;
	$subtitle = sprintf('Catégorie : %s', Tracking::getTaskLabel($id));
}

if ($start = qg('start')) {
	$filters['start'] = $start;
}

if ($end = qg('end')) {
	$filters['end'] = $end;
}

$list = Tracking::getList($filters);
$list->loadFromQueryString();

$filters_uri = http_build_query($filters);
$default_start = (new \DateTime('first day of this year'))->format('d/m/Y');
$default_end = (new \DateTime('last day of this year'))->format('d/m/Y');

$tpl->assign(compact('list', 'filters', 'title', 'subtitle', 'filters_uri', 'default_start', 'default_end'));

$tpl->display(\Paheko\PLUGIN_ROOT . '/templates/all.tpl');
