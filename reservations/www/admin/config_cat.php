<?php

namespace Garradin;

use Garradin\Plugin\Reservations\Reservations;

$session->requireAccess('config', Membres::DROIT_ADMIN);

if (null === qg('id')) {
	throw new UserException('Numéro de catégorie manquant');
}

$r = new Reservations;

$cat = $r->getCategory(qg('id'));

if (f('delete') && $form->check('config_plugin_' . $plugin->id())) {
	$r->deleteCategory($cat->id);
	utils::redirect(utils::plugin_url(['file' => 'config.php']));
}
elseif (f('save') && $form->check('config_plugin_' . $plugin->id())) {
	if (f('champ_actif')) {
		$champ = f('champ');
	}
	else {
		$champ = null;
	}

	$r->updateCategory($cat->id, f('nom'), f('introduction'), f('description'), $champ);
	utils::redirect(utils::plugin_url(['file' => 'config_cat.php', 'query' => sprintf('id=%s&saved', $cat->id)]));
}

$tpl->assign('ok', qg('saved') !== null);
$tpl->assign('category', $cat);
$tpl->display(PLUGIN_ROOT . '/templates/admin/config_cat.tpl');
