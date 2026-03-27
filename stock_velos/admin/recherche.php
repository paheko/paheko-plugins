<?php

namespace Paheko;

require_once __DIR__ . '/_inc.php';

$fields = [];

foreach ($velos->getFields() as $field) {
	if (!$field->enabled) {
		continue;
	}

	if (false !== strpos($field->name, 'date')) {
		continue;
	}

	$fields[$field->name] = $field->label;
}

if (qg('f') && !array_key_exists(qg('f'), $fields))
{
	$_GET['f'] = '';
}

if (qg('q') && qg('f'))
{
	$tpl->assign('liste', $velos->search(qg('f'), qg('q')));
}

$tpl->assign('fields', $fields);

$tpl->assign('current_field', qg('f') ?: 'modele');
$tpl->assign('query', qg('q'));

$tpl->display(PLUGIN_ROOT . '/templates/recherche.tpl');
