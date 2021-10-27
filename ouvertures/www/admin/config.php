<?php

namespace Garradin;

use Garradin\Plugin\Ouvertures\Ouvertures;

$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

if (f('save'))
{
	$form->check('config_plugin_' . $plugin->id(), [
		'open'   => 'array',
		'closed' => 'array',
	]);

	$open_nb = count(f('open')['frequency']);
	$open = [];

	for ($i = 0; $i < $open_nb; $i++)
	{
		$day = f('open')['frequency'][$i];
		$day .= ($day != '') ? ' ' : '';
		$day .= f('open')['day'][$i];

		if (!strtotime($day))
		{
			$form->addError(sprintf('Le sélecteur de jour %s est invalide', $day));
			break;
		}

		$hours = [
			sprintf('%02d:%02d', f('open')['hours_start'][$i], f('open')['minutes_start'][$i]),
			sprintf('%02d:%02d', f('open')['hours_end'][$i], f('open')['minutes_end'][$i]),
		];

		if (!preg_match('/^(2[0-3]|[01][0-9]):([0-5][0-9])/', $hours[0])
			|| !preg_match('/^(2[0-3]|[01][0-9]):([0-5][0-9])/', $hours[1]))
		{
			$form->addError(sprintf('Format d\'heures invalide pour %s', $day));
			break;
		}

		$open[$day] = $hours;
	}

	$closed_nb = f('closed') ? count(f('closed')['day_start']): 0;
	$closed_list = [];

	for ($i = 0; $i < $closed_nb; $i++)
	{
		$closed = [
			sprintf('%s %02d', f('closed')['month_start'][$i], f('closed')['day_start'][$i]),
			sprintf('%s %02d', f('closed')['month_end'][$i], f('closed')['day_end'][$i]),
		];

		if (!strtotime($closed[0]) || !strtotime($closed[1]))
		{
			$form->addError('Format invalide pour jours de fermeture');
		}

		$closed_list[] = $closed;
	}

	if (!$form->hasErrors())
	{
		$plugin->setConfig('open', $open);
		$plugin->setConfig('closed', $closed_list);

		utils::redirect(utils::plugin_url(['file' => 'config.php', 'query' => 'saved']));
	}
}

$tpl->register_function('html_opening_day_select', function ($params) {
	$value = $params['value'];

	if (strchr($value, ' '))
	{
		list($frequency, $day) = explode(' ', $value);
	}
	else
	{
		$frequency = '';
		$day = $value;
	}

	$out = '<select name="open[frequency][]">';

	foreach (Ouvertures::$frequencies as $name => $label)
	{
		$out .= sprintf('<option value="%s"%s>%s</option>',
			htmlspecialchars($name),
			($name == $frequency ? ' selected="selected"' : ''),
			htmlspecialchars($label)
		);
	}

	$out .= '</select> ';

	$out .= '<select name="open[day][]">';

	foreach (Ouvertures::$days as $name => $label)
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
	$hours = explode(':', $params['value']);
	$start_end = $params['start_end'];

	$out = sprintf('<input type="number" name="open[hours_%s][]" min="0" 
		max="23" step="1" required="required" value="%02d" size="2" class="time" pattern="^\d{1,2}$" />',
		$start_end,
		$hours[0]
	);

	$out .= ':';

	$out .= sprintf('<input type="number" name="open[minutes_%s][]" min="0" 
		max="59" step="1" required="required" value="%02d" size="2" class="time" pattern="^\d{1,2}$" />',
		$start_end,
		$hours[1]
	);

	return $out;
});

$tpl->register_function('html_closing_day_select', function ($params) {
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

	$out = sprintf('<input type="number" name="closed[day_%s][]" min="1" 
		max="31" step="1" required="required" value="%02d" class="time" pattern="^\d{1,2}$" /> ',
		$start_end,
		$day
	);

	$out .= sprintf('<select name="closed[month_%s][]">', $start_end);

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

$tpl->assign('config', $plugin->getConfig());
$tpl->assign('example', file_get_contents($plugin->path() . DIRECTORY_SEPARATOR . 'example.skel'));

$tpl->display(PLUGIN_ROOT . '/templates/config.tpl');
