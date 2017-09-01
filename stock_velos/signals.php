<?php

namespace Garradin;

class Velos_Signaux
{
	static public function LoopVelos($params, &$return)
	{
		foreach ($params['loopCriterias'] as $criteria)
		{
			if ($criteria['action'] == \KD2\MiniSkel::ACTION_MATCH_FIELD && $criteria['field'] == 'compter')
			{
				$return['query'] = 'SELECT COUNT(*) AS nb_en_vente FROM plugin_rustine_velos WHERE prix > 0 AND date_sortie IS NULL;';
				break;
			}
			elseif ($criteria['action'] == \KD2\MiniSkel::ACTION_MATCH_FIELD && $criteria['field'] == 'liste')
			{
				$return['query'] = 'SELECT prix, modele, roues, type, genre, etiquette FROM plugin_rustine_velos
					WHERE date_sortie IS NULL AND prix > 0 ORDER BY date_entree ASC;';
				break;
			}
		}

		return true;
	}
}