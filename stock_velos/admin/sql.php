<?php

namespace Paheko;

require_once __DIR__ . '/_inc.php';

$form->runIf(isset($_GET['f'], $_GET['q']), function () use ($tpl, $velos) {
	$tpl->assign('result', $velos->searchSQL(qg('f'), qg('q')));
});

$tpl->assign('fields', qg('f') ?: '*');
$tpl->assign('query', qg('q') !== null ? qg('q') : 'WHERE date_entree > datetime("now", "-1 month")');
$tpl->assign('schema', $velos->getSchemaSQL());

$tpl->display(PLUGIN_ROOT . '/templates/sql.tpl');
