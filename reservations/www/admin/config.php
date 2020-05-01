<?php

namespace Garradin;

use Garradin\Plugin\Reservations\Reservations;

$session->requireAccess('config', Membres::DROIT_ADMIN);

$r = new Reservations;

if (f('save'))
{
	$form->check('config_plugin_' . $plugin->id(), [
		'slot'   => 'array|required',
		'text' => 'string|required',
	]);

	if (!$form->hasErrors())
	{
		$plugin->setConfig('text', $_POST['text']);

		$i = 0;

		foreach (f('slot') as $id => $props) {
			$i++;
			$props = (object)$props;
			$props->repetition = !empty($props->repetition);

			if (!isset($props->repetition, $props->jour, $props->heure, $props->maximum)) {
				$form->addError('Erreur à la ligne ' . $i);
				continue;
			}

			try {
				if ('_' === substr($id, 0, 1)) {
					$r->createSlot($props->jour, $props->heure, $props->repetition, (int)$props->maximum);
				}
				else {
					$r->updateSlot((int)$id, $props->jour, $props->heure, $props->repetition, (int)$props->maximum);
				}
			}
			catch (UserException $e) {
				$form->addError(sprintf('Ligne %d: %s', $i, $e->getMessage()));
			}
		}

		if (!$form->hasErrors()) {
			utils::redirect(utils::plugin_url(['file' => 'config.php', 'query' => 'saved']));
		}
	}
}

$slots = $r->listSlots();

if (!count($slots)) {
	$slots = [];
	$slots[] = (object)['id' => '_1', 'jour' => '', 'heure' => '', 'maximum' => '', 'repetition' => 0];
}

$tpl->assign('config', $plugin->getConfig());
$tpl->assign('slots', $slots);
$tpl->assign('ok', qg('saved') !== null);

//$tpl->assign('example', file_get_contents($plugin->path() . DIRECTORY_SEPARATOR . 'example.skel'));


$tpl->display(PLUGIN_ROOT . '/templates/admin/config.tpl');
