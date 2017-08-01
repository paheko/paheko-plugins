<?php

namespace Garradin;

use KD2\MiniSkel;

class Ouvertures_Signals
{
	static public $config;

	static public function boucle(array &$params, array &$return)
	{
		foreach ($params['loopCriterias'] as $criteria)
		{
			if ($criteria['action'] != MiniSkel::ACTION_MATCH_FIELD)
			{
				continue;
			}

			$action = $criteria['field'];
			break;
		}

		if (!$action)
		{
			$action = 'liste';
		}

		$code = new Squelette_Snippet;
		$code->append(1, 'if (!isset($ouvertures)):');
		$code->append(1, '  $ouvertures = new Plugin(\'ouvertures\');');
		$code->append(1, '  require_once $ouvertures->path() . DIRECTORY_SEPARATOR . \'lib/Ouvertures.php\';');
		$code->append(1, 'endif;');
		$code->append(1, sprintf('$OBJ_VAR = new Plugin\Ouvertures\Ouvertures(%s);', var_export($action, true)));
		$code->append(1, '$nb_rows = $OBJ_VAR->countRows();');

		$return['statement_code'] = $out->output(true);

		return true;
	}
}

Ouvertures::$config = $plugin->getConfig();