<?php

namespace Paheko\Plugin\Welcome;

use Paheko\Entities\Signal;

use Paheko\Template;

class Signaux
{
	static public function banner(Signal $signal)
	{
		$html = Template::getInstance()->fetch(__DIR__ . '/../templates/banner.tpl');
		$signal->setOut('welcome', $html);
	}
}
