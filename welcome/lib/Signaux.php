<?php

namespace Garradin\Plugin\Welcome;

use Garradin\{Template, Membres\Session, Membres};

class Signaux
{
	static public function banner(array $params, &$return)
	{
		$session = new Session;

		if (!$session->isLogged() || !$session->canAccess('config', Membres::DROIT_ADMIN)) {
			return;
		}

		$return = Template::getInstance()->fetch($params['plugin_root'] . '/templates/banner.tpl');
	}
}
