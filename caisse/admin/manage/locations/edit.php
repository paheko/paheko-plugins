<?php

namespace Paheko;
use Paheko\Plugin\Caisse\Locations;

require __DIR__ . '/../_inc.php';

if (qg('new') !== null) {
	$location = Locations::new();
	$csrf_key = 'location_new';
}
else {
	$location = Locations::get((int) qg('id'));
	$csrf_key = 'location_edit_' . $location->id();
}

$tpl->assign(compact('location', 'csrf_key'));

if (qg('delete') !== null) {
	$form->runIf('delete', function () use ($location) {
		if (!f('confirm_delete')) {
			throw new UserException('Merci de cocher la case pour confirmer la suppression.');
		}

		$location->delete();
	}, $csrf_key, './');

	$tpl->display(PLUGIN_ROOT . '/templates/manage/locations/delete.tpl');
}
else {
	$form->runIf('save', function () use ($location) {
		$location->importForm();
		$location->save();
	}, $csrf_key, './');

	$tpl->assign(compact('location'));

	$tpl->display(PLUGIN_ROOT . '/templates/manage/locations/edit.tpl');
}
