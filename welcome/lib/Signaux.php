<?php

namespace Garradin\Plugin\Welcome;

use Garradin\{Template, Membres\Session, Membres};

class Signaux
{
	static public function banner(array $params, &$return)
	{
		$session = Session::getInstance();

		if (!$session->isLogged() || !$session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)) {
			return;
		}

		$return = Template::getInstance()->fetch($params['plugin_root'] . '/templates/banner.tpl');
	}
}
