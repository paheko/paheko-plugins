<?php

namespace Garradin;

use Garradin\Plugin\Stock_Velos\Velos;

$session->requireAccess('membres', Membres::DROIT_ECRITURE);

$velos = new Velos;

$tpl->assign('plugin_css', ['style.css']);
$tpl->assign('plugin_tpl', PLUGIN_ROOT . '/templates/');

function tpl_form_select(array $params)
{
	if (empty($params['name']))
	{
		throw new \BadFunctionCallException("Paramètre name manquant pour select");
	}

	$name = $params['name'];
	$value = '';

	if (f($name) !== null)
	{
		$value = f($name);
	}
	elseif (isset($params['data']) && is_array($params['data']) && isset($params['data'][$name]))
	{
		$value = $params['data'][$name];
	}
	elseif (isset($params['data']) && is_object($params['data']) && isset($params['data']->$name))
	{
		$value = $params['data']->$name;
	}
	elseif (isset($params['default']))
	{
		$value = $params['default'];
	}

	$out = sprintf('<select name="%s" id="f_%1$s">', $params['name']);

	if (!empty($params['values']))
	{
		foreach ($params['values'] as $v)
		{
			$out .= '<option value="'.htmlspecialchars($v, ENT_QUOTES, 'UTF-8').'"';

			if ($v == $value)
				$out .= ' selected="selected"';

			$out .= '>'.htmlspecialchars($v, ENT_QUOTES, 'UTF-8').'</option>';
		}
	}

	$out .= '</select>';
	return $out;
}

$tpl->register_function('form_select', 'Garradin\tpl_form_select');
