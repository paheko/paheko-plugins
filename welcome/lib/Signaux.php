<?php

namespace Garradin\Plugin\Welcome;

use Garradin\Template;

class Signaux
{
	static public function banner(array $params, &$return)
	{
		$return = Template::getInstance()->fetch($params['plugin_root'] . '/templates/banner.tpl');
	}
}
