<?php

namespace Garradin;

require_once __DIR__ . '/_inc.php';

if (f('save') && $form->check('ajout_velos'))
{
    try {
        $velos->addVelosDemontes(f('nb'), f('source'), f('source_details'));
        utils::redirect(utils::plugin_url());
    }
    catch (UserException $e)
    {
        $form->addError($e->getMessage());
    }
}

$tpl->assign('sources', $velos->listSources());

$tpl->display(PLUGIN_ROOT . '/templates/ajout_demontage.tpl');
