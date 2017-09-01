<?php

namespace Garradin;

require_once __DIR__ . '/_inc.php';

$id = qg('id');

$velo = $velos->getVelo($id);

if (!$velo)
    throw new UserException('Ce vélo n\'existe pas !');

if (empty($velo->date_sortie) || ($velo->raison_sortie != 'Vendu' && $velo->raison_sortie != 'Vendu en bourse'))
    throw new UserException('Impossible de racheter un vélo qui n\'est pas vendu !');

if (!$velos->checkRachatVelo($velo->id))
{
    throw new UserException('Le vélo a déjà été racheté !');
}

if (f('buy') && $form->check('rachat_velo_'.$velo->id))
{
    $data = array(
        'etiquette'     =>  (int) f('etiquette'),
        'source'        =>  'Rachat',
        'source_details'=>  $velo['id'],
        'type'          =>  $velo['type'],
        'genre'         =>  $velo['genre'],
        'roues'         =>  $velo['roues'],
        'couleur'       =>  $velo['couleur'],
        'modele'        =>  $velo['modele'],
        'date_entree'   =>  gmdate('Y-m-d'),
        'etat_entree'   =>  f('etat'),
        'notes'         =>  'Racheté à l\'adhérent pour '.floatval(f('prix')).' €',
    );

    try {
        $velos->checkData($data);
        $id = $velos->addVelo($data);
        utils::redirect(utils::plugin_url([
            'file' => 'rachat_ok.php',
            'query' => 'id=' . $id .
                '&prix=' . rawurlencode(floatval(utils::post('prix')))
            ]
        ));
    }
    catch (UserException $e)
    {
        $form->addError($e->getMessage());
    }
}

$tpl->assign('velo', $velo);
$tpl->assign('prix', round($velo->prix / 3));
$tpl->assign('libre', $velos->getEtiquetteLibre());
$tpl->assign('adherent', $velos->getMembre($velo['details_sortie']));

$tpl->display(PLUGIN_ROOT . '/templates/rachat.tpl');
