<?php

namespace Garradin\Plugin\Ouvertures;

use Garradin\Plugin;
use KD2\MiniSkel;

class Ouvertures
{
	static protected $config;
	static protected $now;

	static public $frequencies = [
		''  => 'tous les',
		'first'  => 'premier',
		'second' => 'second',
		'third'  => 'troisième',
		'fourth' => 'quatrième',
		'fifth'  => 'cinquième',
		'last'   => 'dernier',
	];

	static public $days = [
		'monday'    => 'lundi',
		'tuesday'   => 'mardi',
		'wednesday' => 'mercredi',
		'thursday'  => 'jeudi',
		'friday'    => 'vendredi',
		'saturday'  => 'samedi',
		'sunday'    => 'dimanche',
	];

	static protected function storeConfig()
	{
		$plugin = new Plugin('ouvertures');
		$config = $plugin->getConfig();
		unset($plugin);

		$config->open = $config->open;

		$config->open_days = [];

		foreach ($config->open as $row)
		{
			$day = $row->day ? $row->day . ' ' : '';
			$config->open_days[] = [strtotime($day . $row->open), strtotime($day . $row->close), $row->day];
		}

		foreach ($config->closed as &$row)
		{
			$row = [strtotime($row[0]), strtotime($row[1] . ', 23:59:59')];

			if (date('m', $row[1]) < date('m', $row[0]))
			{
				$row[1] = strtotime(date('Y-m-d', $row[1]) . ' +1 year');
			}
		}

		self::$now = time();

		self::$config = $config;
		return true;
	}

	static public function registerTemplate(array $params)
	{
		$ut =& $params['template'];

		$ut->registerSection('opening_hours', [self::class, 'section']);
	}

	static public function section(array $params)
	{
		$when = $params['when'] ?? null;

		if (!self::$config)
		{
			self::storeConfig();
		}

		foreach (self::getList($when) as $row) {
			yield $row;
		}
	}

	static public function getList(?string $when): array
	{
		if (!$when) {
			$when = 'week';
		}

		$data = [];
		$open = self::$config->open_days;

		// All opening days
		if ($when == 'open')
		{
			foreach ($open as $day => $hours)
			{
				$data[] = ['opening_time' => $hours[0], 'closing_time' => $hours[1], 'opening_day' => $hours[2]];
			}
		}
		// All closing days
		elseif ($when == 'closings')
		{
			foreach (self::$config->closed as $hours)
			{
				$data[] = ['start_date' => $hours[0], 'end_date' => $hours[1]];
			}
		}

		unset($hours);

		// Next opening day
		if ($when == 'next' || $when == 'now')
		{
			// trier du plus petit au plus grand
			// la prochaine ouverture devrait donc être au début
			uasort($open, function ($a, $b) {
				if ($a[0] == $b[0]) return 0;
				return $a[0] > $b[0] ? 1 : -1;
			});

		}

		// Are we open now?
		if ($when == 'now')
		{
			foreach (self::$config->closed as $hours)
			{
				if (self::$now >= $hours[0] && self::$now <= $hours[1])
				{
					// En période de fermeture
					return [];
				}
			}

			foreach ($open as $hours)
			{
				if (self::$now >= $hours[0] && self::$now <= $hours[1])
				{
					$data[] = ['opening_time' => $hours[0], 'closing_time' => $hours[1], 'opening_day' => $hours[2]];
					break;
				}
			}
		}
		elseif ($when == 'next')
		{
			$next = null;

			$i = 0;

			while (!$next && $i++ < 10)
			{
				foreach ($open as $day => $hours)
				{
					// Nous avons trouvé la première ouverture qui suit l'heure courante
					if ($hours[0] > self::$now)
					{
						// On vérifie qu'elle n'est pas dans une période de fermeture
						foreach (self::$config->closed as $closed)
						{
							if ($hours[0] >= $closed[0] && $hours[0] <= $closed[1])
							{
								continue(2);
							}
						}

						$next = ['opening_time' => $hours[0], 'closing_time' => $hours[1], 'opening_day' => $hours[2]];
						break(2);
					}
				}

				// On n'a pas trouvé d'ouverture, sûrement à cause des fermetures !
				// On va donc chercher sur les créneaux suivants
				if (!$next)
				{
					foreach ($open as &$hours)
					{
						$day = $hours[2];

						// Find the next opening after current one
						if (strstr($day, ' '))
						{
							// last tuesday of next month 2017-09 => 2017-10-31
							// second sunday of next month 2017-07 => 2017-08-13
							$day = sprintf('%s of next month %s', $day, date('Y-m', $hours[0]));
						}
						else
						{
							// next tuesday 2017-08-01 => 2017-08-08
							$day = sprintf('next %s %s', $day, date('Y-m-d', $hours[0]+60*60*24));
						}

						$hours = [
							strtotime($day . date(' H:i', $hours[0])),
							strtotime($day . date(' H:i', $hours[1])),
							$hours[2],
						];
					}

					unset($hours);
				}
			}

			if ($next)
			{
				$data[] = $next;
			}
		}
		// All days of the week
		elseif ($when == 'week')
		{
			foreach ($open as $slot) {
				$data[$slot[2]] = [
					'opening_day' => $slot[2],
					'opening_time' => null,
					'closing_time' => null,
				];
			}

			foreach (self::$config->open as $hours)
			{
				$data[$hours[2]] = ['opening_time' => $hours[0], 'closing_time' => $hours[1], 'opening_day' => $hours[2]];
			}

			$data = array_values($data);
		}

		foreach ($data as &$row)
		{
			if (!empty($row['opening_day']))
			{
				if (strchr($row['opening_day'], ' '))
				{
					list($freq, $day) = explode(' ', $row['opening_day']);

					$row['opening_day'] = sprintf('%s %s', self::$frequencies[$freq], self::$days[$day]);
				}
				else
				{
					$row['opening_day'] = self::$days[$row['opening_day']];
				}
			}
		}

		return $data;
	}
}
