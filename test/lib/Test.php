<?php

namespace Paheko\Plugin\Test;

use Paheko\Entities\Plugin;
use Paheko\Entities\Signal;
use Paheko\Users\Session;
use Paheko\UserTemplate\CommonFunctions;

class Test
{
	static public function homeButton(Signal $signal, Plugin $plugin): void
	{
		// Désactiver l'affichage du bouton
		if (empty($plugin->config->display_button)) {
			return;
		}

		$html = CommonFunctions::linkbutton([
			'label' => 'Test !',
			'icon' => Plugins::getPrivateURL('test', 'icon.svg'),
			'href' => Plugins::getPrivateURL('test'),
		]);

		// On ajoute notre bouton sur la page d'accueil
		$plugin->setOut('test', $html);
	}
}