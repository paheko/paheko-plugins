<?php

namespace Garradin\Plugin\Ouvertures;

use Garradin\Plugin;

class Ouvertures
{
	static protected $config;
	static protected $now;

	protected $data = [];
	protected $i = 0;

	public function __construct($type)
	{
		if (!self::$config)
		{
			$this->storeConfig();
		}

		if ($type == 'liste')
		{
			foreach (self::$config->open as $hours)
			{
				$this->data[] = ['date_ouverture' => $hours[0], 'date_fermeture' => $hours[1]];
			}
		}
		elseif ($type == 'fermetures')
		{
			foreach (self::$config->closed as $hours)
			{
				$this->data[] = ['date_debut' => $hours[0], 'date_fin' => $hours[1]];
			}
		}
		elseif ($type == 'maintenant')
		{
			foreach (self::$config->closed as $hours)
			{
				if (self::$now >= $hours[0] && self::$now <= $hours[1])
				{
					// En période de fermeture
					return $this;
				}
			}

			foreach (self::$config->open as $hours)
			{
				if (self::$now >= $hours[0] && self::$now <= $hours[1])
				{
					$this->data[] = ['date_ouverture' => $hours[0], 'date_fermeture' => $hours[1]];
					break;
				}
			}
		}
		elseif ($type == 'prochaine')
		{
			$next = null;

			$open = self::$config->open;
			$i = 0;

			while (!$next && $i++ < 10)
			{
				foreach ($open as $hours)
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

						$next = ['date_ouverture' => $hours[0], 'date_fermeture' => $hours[1]];
						break(2);
					}
				}

				// On n'a pas trouvé d'ouverture, sûrement à cause des fermetures !
				// On va donc chercher sur les créneaux suivants
				if (!$next)
				{
					foreach ($open as $day => &$hours)
					{
						// Find the next opening after current one
						if (strstr(' ', $day))
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
						];
					}
				}
			}

			if ($next)
			{
				$this->data[] = $next;
			}
		}
	}

	public function countRows()
	{
		return count($this->data);
	}

	public function fetchArray($mode = null)
	{
		if ($this->i >= count($this->data))
		{
			return false;
		}

		return $this->data[$this->i++];
	}

	protected function storeConfig()
	{
		$plugin = new Plugin('ouvertures');
		$config = $plugin->getConfig();
		unset($plugin);

		$config->open = (array) $config->open;

		foreach ($config->open as $day => &$row)
		{
			$day = $day ? $day . ' ' : '';
			$row = [strtotime($day . $row[0]), strtotime($day . $row[1])];
		}

		// trier du plus petit au plus grand
		// la prochaine ouverture devrait donc être au début
		uasort($config->open, function ($a, $b) {
			if ($a[0] == $b[0]) return 0;
			return $a[0] > $b[0] ? 1 : -1;
		});

		foreach ($config->closed as &$row)
		{
			$row = [strtotime($row[0]), strtotime($row[1] . ' 23:59:59')];
		}

		self::$now = time();

		self::$config = $config;
		return true;
	}
}