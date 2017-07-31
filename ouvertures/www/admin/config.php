<?php

namespace Garradin;

$session->requireAccess('config', Membres::DROIT_ADMIN);

if (f('save'))
{
	$form->check('plugin_save', [
		'open'   => 'array|required',
		'closed' => 'array',
	]);

	foreach (f('open') as $day => &$hours)
	{
		if (!strtotime($day))
		{
			$form->addError(sprintf('Le sélecteur de jour %s est invalide', $day));
			break;
		}

		$hours = array_values($hours);

		if (count($hours) != 2)
		{
			$form->addError(sprintf('Format d\'heures invalide pour %s', $day));
			break;
		}

		if (!preg_match('/^(2[0-3]|[01][0-9]):([0-5][0-9])/', $hours[0])
			|| !preg_match('/^(2[0-3]|[01][0-9]):([0-5][0-9])/', $hours[1]))
		{
			$form->addError(sprintf('Format d\'heures invalide pour %s', $day));
			break;
		}
	}

	foreach (f('closed') as &$closed)
	{
		$closed = array_values($closed);

		if (!strtotime($closed[0]) || !strtotime($closed[1]))
		{
			$form->addError('Format invalide pour jours de fermeture');
		}
	}

	if (!$form->hasErrors())
	{
		$plugin->setConfig('open', f('open'));
		$plugin->setConfig('closed', f('closed'));
		$plugin->setConfig('timezone', f('timezone'));

		utils::redirect(utils::plugin_url());
	}
}

$tpl->register_function('html_opening_day_select', function ($params) {
	$key = $params['key'];
	$params['value'];

	static $frequencies = [
		'every'  => 'tous les',
		'first'  => 'premiers',
		'second' => 'seconds',
		'third'  => 'troisièmes',
		'fourth' => 'quatrièmes',
		'fifth'  => 'cinquièmes',
		'last'   => 'derniers',
	];

	static $days = [
		'day'       => 'jours',
		'monday'    => 'lundis',
		'tuesday'   => 'mardis',
		'wednesday' => 'mercredis',
		'thursday'  => 'jeudis',
		'friday'    => 'vendredis',
		'saturday'  => 'samedis',
		'sunday'    => 'dimanches',
	];

	$out = sprintf('<select name="open[%d][frequency]">', $key);

	foreach ($frequencies as $name => $label)
	{
		$out .= sprintf('<option value="%s"%s>%s</option>',
			htmlspecialchars($name),
			($name == $frequency ? ' selected="selected"' : ''),
			htmlspecialchars($label)
		);
	}

	$out .= '</select> ';

	$out .= sprintf('<select name="open[%d][day]">', $key);

	foreach ($days as $name => $label)
	{
		$out .= sprintf('<option value="%s"%s>%s</option>',
			htmlspecialchars($name),
			($name == $day ? ' selected="selected"' : ''),
			htmlspecialchars($label)
		);
	}

	$out .= '</select> du mois';

	return $out;
});

$tpl->register_function('html_opening_hour_select', function ($params) {
	$key = $params['key'];
	$hours = explode(':', $params['value']);

	$out = sprintf('<input type="number" name="open[%d][start][hours]" min="0" 
		max="23" step="1" required="required" value="%d" />',
		$key,
		$hours[0]
	);

	$out .= ':';

	$out = sprintf('<input type="number" name="open[%d][start][minutes]" min="0" 
		max="59" step="1" required="required" value="%d" />',
		$key,
		$hours[0]
	);

	return $out;
});

$tpl->register_function('html_closing_day_select', function ($params) {
	$key = $params['key'];
	$start_end = $params['start_end'];

	list($month, $day) = explode(' ', $params['value']);

	static $months = [
		'january'   => 'janvier',
		'february'  => 'février',
		'march'     => 'mars',
		'april'     => 'avril',
		'may'       => 'mai',
		'june'      => 'juin',
		'july'      => 'juillet',
		'august'    => 'août',
		'september' => 'septembre',
		'october'   => 'octobre',
		'november'  => 'novembre',
		'december'  => 'décembre',
	];

	$out = sprintf('<input type="number" name="closed[%d][%s][hours]" min="1" 
		max="31" step="1" required="required" value="%d" /> ',
		$key,
		$start_end,
		$day
	);

	$out .= sprintf('<select name="closed[%d][%s][month]">', $key, $start_end);

	foreach ($months as $name => $label)
	{
		$out .= sprintf('<option value="%s"%s>%s</option>',
			htmlspecialchars($name),
			($name == $month ? ' selected="selected"' : ''),
			htmlspecialchars($label)
		);
	}

	$out .= '</select>';

	return $out;
});

$tpl->register_function('html_timezone_select', function ($params) {
	$out .= '<select name="timezone">';

	foreach (timezone_identifiers_list() as $label)
	{
		$out .= sprintf('<option value="%s"%s>%s</option>',
			htmlspecialchars($label),
			($label == $params['value'] ? ' selected="selected"' : ''),
			htmlspecialchars($label)
		);
	}

	$out .= '</select>';
	return $out;
});

$tpl->assign('error', $error);

$tpl->display(PLUGIN_ROOT . '/templates/config.tpl');
