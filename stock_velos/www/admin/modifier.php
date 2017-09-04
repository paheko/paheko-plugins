<?php

namespace Garradin;

require_once __DIR__ . '/_inc.php';

if (!qg('id'))
    throw new UserException('Manque ID dans URL');

$id = (int) qg('id');

$velo = $velos->getVelo($id);

if (!$velo)
    throw new UserException('Ce vélo n\'existe pas !');

if (f('save') && $form->check('modif_velo'))
{
    $data = [
        'etiquette'     =>  (int) f('etiquette'),
        'bicycode'      =>  f('bicycode'),
        'prix'          =>  (double) f('prix'),
        'source'        =>  f('source'),
        'source_details'=>  f('source_details'),
        'type'          =>  f('type'),
        'genre'         =>  f('genre'),
        'roues'         =>  f('roues'),
        'couleur'       =>  f('couleur'),
        'modele'        =>  f('modele'),
        'date_entree'   =>  f('date_entree'),
        'etat_entree'   =>  f('etat_entree'),
        'date_sortie'   =>  f('date_sortie'),
        'raison_sortie' =>  f('raison_sortie'),
        'details_sortie'=>  f('details_sortie'),
        'notes'         =>  f('notes'),
    ];

    try {
        $velos->checkData($data);
        $velos->editVelo($id, $data);
        utils::redirect(utils::plugin_url(['query' => 'id=' . $id]));
    }
    catch (UserException $e)
    {
        $form->addError($e->getMessage());
    }
}

$tpl->assign('sources', $velos->listSources());
$tpl->assign('types', $velos->listTypes());
$tpl->assign('genres', $velos->listGenres());
$tpl->assign('roues', $velos->listTailles());
$tpl->assign('raisons_sortie', $velos->listRaisonsSortie());

$tpl->assign('velo', $velo);

$tpl->display(PLUGIN_ROOT . '/templates/modifier.tpl');
