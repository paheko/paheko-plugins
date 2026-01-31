<?php

namespace Paheko;

use Paheko\Plugin\Caisse\Tabs;

require_once __DIR__ . '/_inc.php';

$query = trim($_POST['q'] ?? '');
$list = [];

// Recherche simple
if ($query !== '') {
	$list = Tabs::searchUserWithServices($query);
}

$tpl->assign(compact('query', 'list'));

$tpl->display(PLUGIN_ROOT . '/templates/user_search.tpl');