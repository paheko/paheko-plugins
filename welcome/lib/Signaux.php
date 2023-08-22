<?php

namespace Paheko\Plugin\Welcome;

use Paheko\Template;

class Signaux
{
	static public function banner(Signnal $signal)
	{
		$html = Template::getInstance()->fetch(__DIR__ . '/../templates/banner.tpl');
		$signal->setOut('welcome', $html);
	}
}
