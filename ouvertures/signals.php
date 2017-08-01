<?php

namespace Garradin\Plugin\Ouvertures;

use KD2\MiniSkel;

class Signals
{
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

		$return['code'] = sprintf('$OBJ_VAR = new Plugin\Ouvertures\Ouvertures(%s);', var_export($action, true));

		return true;
	}
}