<?php

namespace Garradin;

require_once __DIR__ . '/_inc.php';

if (qg('etiquette'))
{
    $id = $velos->getIdFromEtiquette(qg('etiquette'));
}
else
{
	$id = qg('id');
}

if (!$id)
    throw new UserException('Impossible de trouver le vélo indiqué');

$velo = $velos->getVelo($id);

if (!$velo)
    throw new UserException('Ce vélo n\'existe pas !');

$tpl->assign('velo', $velo);

if ($velo->source == 'Don' && is_numeric($velo->source_details))
{
	$tpl->assign('source_membre', $velos->getMembre($velo->source_details));
}

if ($velo->raison_sortie == 'Vendu' && is_numeric($velo->details_sortie))
{
	$tpl->assign('sortie_membre', $velos->getMembre($velo->details_sortie));
}

if (!$velos->checkRachatVelo($id))
{
    $tpl->assign('rachat', $velos->getRachatVelo($id));
}

$tpl->display(PLUGIN_ROOT . '/templates/fiche.tpl');
