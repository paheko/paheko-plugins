<?php

namespace Garradin;

$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

if (f('save'))
{
    $form->check('config_plugin_' . $plugin->id(), [
        'display_hello' => 'boolean',
    ]);

    if (!$form->hasErrors())
    {
        try {
            $plugin->setConfig('display_hello', (bool) f('display_hello'));
            utils::redirect(utils::plugin_url());
        }
        catch (UserException $e)
        {
            $form->addError($e->getMessage());
        }
    }
}

$tpl->display(PLUGIN_ROOT . '/templates/config.tpl');
