<?php

namespace Garradin;

require_once __DIR__ . '/_inc.php';

$velo = $velos->getVelo(qg('id'));

if (!$velo)
    throw new UserException('Ce vélo n\'existe pas !');

if (!empty($velo->date_sortie))
    throw new UserException('Ce vélo ne peut être racheté.');

$tpl->assign('velo', $velo);
$tpl->assign('adherent', $velos->getMembre($velos->getRacheteurVelo($velo->source_details)));
$tpl->assign('prix', qg('prix'));

$tpl->display(PLUGIN_ROOT . '/templates/rachat_ok.tpl');
