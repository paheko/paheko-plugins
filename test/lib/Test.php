<?php

namespace Garradin\Plugin\Test;

use Garradin\Plugin;
use Garradin\Users\Session;
use Garradin\UserTemplate\CommonFunctions;

class Test
{
	static public function homeButton(array $params, array &$buttons): void
	{
		$plugin = new Plugin('test');

		// Désactiver l'affichage du bouton
		if (!$plugin->getConfig('display_button')) {
			return;
		}

		// On ajoute notre bouton sur la page d'accueil
		$buttons['test'] = CommonFunctions::linkbutton([
			'label' => 'Test !',
			'shape' => 'settings',
			'href' => Plugin::getURL('test'),
		]);
	}


	static public function menuItem(array $params, array &$list): void
	{
		// On exige que l'utilisateur connecté ait accès en lecture aux membres
		if (!Session::getInstance()->canAccess(Session::SECTION_USERS, Session::ACCESS_READ)) {
			return;
		}

		$list['plugin_test'] = sprintf('<a href="%s">Test !</a>', Plugin::getURL('test'));
	}

}