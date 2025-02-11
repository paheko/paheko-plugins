<?php

namespace Paheko\Plugin\PIM;

use DateTime;
use DateInterval;

class Calendar
{
	const WORLD_CELEBRATIONS = [
		1 => [
			1 => 'Journée mondiale de la paix',
			4 => 'Journée mondiale du braille',
			7 => 'Fête de la quenouille',
			15 => 'Journée de Wikipédia',
			20 => 'Martin Luther King Day',
			21 => 'Journée internationale des câlins',
			22 => 'Journée de l\'amitié franco-allemande',
			26 => 'Journée internationale des énergies propres',
			27 => 'Journée internationale dédiée à la mémoire des victimes de l\'Holocauste',
			28 => 'Journée européenne de la protection des données',
		],
		2 => [
			2 => 'Journée mondiale des zones humides',
			4 => 'Journée mondiale contre le cancer',
			6 => 'Journée mondiale sans téléphone portable',
			7 => 'Journée mondiale sans téléphone portable',
			8 => 'Journée mondiale sans téléphone portable',
			11 => 'Journée mondiale des malades',
			20 => 'Journée mondiale de la justice sociale',
			21 => 'Journée internationale de la langue maternelle',
		],
		3 => [
			3 => 'Journée mondiale de la vie sauvage',
			5 => 'Journée internationale pour le désarmement et la non-prolifération',
			8 => 'Journée internationale des femmes',
			14 => 'Journée de π',
			15 => 'Journée internationale de lutte contre l\'islamophobie',
			20 => 'Journée mondiale du bonheur',
			21 => 'Journée internationale pour l\'élimination de la discrimination raciale',
			22 => 'Journée mondiale de l\'eau',
			30 => 'Journée internationale du zéro déchet',
		],
		4 => [
			2 => 'Journée mondiale de la sensibilisation à l\'autisme',
			7 => 'Journée mondiale de la santé',
			8 => 'Journée internationale des Roms',
			12 => 'Nuit de Youri Gagarine',
			14 => 'Journée nationale du souvenir de la déportation',
			22 => 'Journée de la terre',
			24 => 'Journée de commémoration du génocide arménien',
			28 => 'Journée mondiale contre les accidents du travail',
			29 => 'Journée internationale de la danse',
		],
		5 => [
			1 => 'Journée internationale des travailleurs',
			3 => 'Journée mondiale de la liberté de la presse',
			6 => 'Journée internationale sans régime',
			7 => 'Journée mondiale des orphelins du sida',
			9 => 'Journée de l\'Europe',
			12 => 'Journée internationale des infirmières',
			17 => 'Journée mondiale de lutte contre l\'homophobie',
			20 => 'Journée mondiale des abeilles',
			21 => 'Journée internationale du thé',
			22 => 'Journée internationale de la biodiversité',
			25 => 'Jour de la serviette',
			26 => 'National Sorry Day (Australie)',
			30 => 'Journée internationale de la patate',
			31 => 'Journée mondiale sans tabac',
		],
		6 => [
			3 => 'Journée mondiale du vélo',
			5 => 'Journée mondiale de l\'environnement',
			8 => 'Journée mondiale des océans',
			12 => 'Journée mondiale contre le travail des enfants',
			14 => 'Journée mondiale du donneur de sang',
			16 => 'Journée de l’enfant africain',
			18 => 'Journée internationale contre les discours haineux',
			20 => 'Journée mondiale des réfugiés',
			21 => 'Fête de la musique',
			26 => 'Journée internationale contre la torture',
			28 => 'Marche des fiertés',
			30 => 'Journée internationale des astéroïdes',
		],
		7 => [
			20 => 'Journée internationale de la lune',
			30 => 'Journée internationale de l\'amitié',
		],
		8 => [
			9 => 'Journée internationale des populations autochtones',
			12 => 'Journée internationale de la jeunesse',
			13 => 'Journée internationale des gauchers',
			19 => 'Journée mondiale de l\'aide humanitaire',
			23 => 'Journée européenne du souvenir',
			29 => 'Journée internationale contre les essais nucléaires',
		],
		9 => [
			1 => 'Journée internationale de l\'alphabétisation',
			19 => 'International Talk Like a Pirate Day',
			21 => 'Journée internationale de la paix',
			22 => 'Journée sans voiture',
			23 => 'Journée de la bisexualité',
			26 => 'Journée européenne des langues',
			30 => 'Journée nationale de la vérité et de la réconciliation (Canada)',
		],
		10 => [
			1 => 'Journée internationale pour les personnes âgées',
			2 => 'Journée internationale de la non-violence',
			4 => 'Journée mondiale des animaux',
			5 => 'Journée mondiale des enseignants',
			10 => 'Journée mondiale contre la peine de mort',
			11 => 'Journée internationale des droits des filles',
			16 => 'Journée mondiale de l\'alimentation',
			17 => 'Journée internationale pour l\'élimination de la pauvreté',
			19 => 'Journée mondiale des toilettes',
			23 => 'Journée de la Mole',
			24 => 'Journée des Nations unies',
			25 => 'Journée européenne de la justice',
			28 => 'Journée mondiale du cinéma d\'animation',
		],
		11 => [
			1 => 'Journée internationale du végétalisme',
			2 => 'Journée internationale pour la fin de l\'impunité pour les crimes contre les journalistes',
			5 => 'Journée mondiale de sensibilisation aux tsunamis',
			10 => 'Journée mondiale de la science pour la paix',
			16 => 'Journée internationale de la tolérance',
			17 => 'Journée mondial du souvenir des victimes d\'accidents de la route',
			20 => 'Journée internationale des droits de l\'enfant',
			25 => 'Journée internationale pour l\'élimination de la violence à l\'égard des femmes',
			26 => 'Journée mondiale du transport soutenable',
			29 => 'Journée internationale de solidarité avec le peuple palestinien',
		],
		12 => [
			1 => 'Journée mondiale de lutte contre le sida',
			2 => 'Journée internationale de l\'abolition de l\'esclavage',
			10 => 'Journée des droits humains',
			11 => 'Journée internationale de la montagne',
			18 => 'Journée internationale des migrants',
			20 => 'Journée de la solidarité entre humains',
			21 => 'Journée mondiale de l\'orgasme',
			27 => 'Journée internationale de préparation aux épidémies',
		],
	];

	const FR_HOLIDAYS = [
		'01-01' =>  'Jour de l\'an',
		'05-01' =>  'Fête des travailleurs⋅euses',
		'05-08' =>  'Victoire',
		'07-14' =>  'Fête nationale',
		'08-15' =>  'Assomption',
		'11-11' =>  'Armistice',
		'11-01' =>  'Toussaint',
		'12-25' =>  'Noël',
	];

	const NZ_HOLIDAYS = [
		'01-01' => 'New Year\'s Day',
		'01-02' => 'Day after New Year\'s Day',
		'02-06' => 'Waitangi Day',
		'04-25' => 'Anzac Day',
		'12-25' => 'Christmas',
		'12-26' => 'Boxing Day',
	];

	static public function getNZPublicHolidays($year)
	{
		static $holidays = [];

		if (isset($holidays[$year])) {
			return $holidays[$year];
		}

		$holidays[$year] = [];

		// Décalage des dates, un jour férié qui tombe un weekend est reporté
		foreach (self::NZ_HOLIDAYS as $date => $name)
		{
			$datetime = DateTime::createFromFormat('Y-m-d', $year . '-' . $date);

			if ($date == '01-02' || $date == '12-26')
			{
				// If end up on a Saturday, then it's the next Monday
				// If on Sunday, then it's the next Tuesday (so + days)
				if ($datetime->format('N') >= 6)
				{
					$datetime->modify('+2 days');
				}
				// If it's on a monday, shift to Tuesday, as christmas was on Monday
				elseif ($datetime->format('N') == 1)
				{
					$datetime->modify('+1 day');
				}
			}
			else
			{
				// Move to the next Monday
				if ($datetime->format('N') == 6)
				{
					$datetime->modify('+2 days');
				}
				elseif ($datetime->format('N') == 7)
				{
					$datetime->modify('+1 days');
				}
			}

			if ($datetime->format('m-d') != $date)
			{
				$holidays[$year][$datetime->format('m-d')] = $name;
			}
			else
			{
				$holidays[$year][$date] = $name;
			}
		}

		// Add variable holidays
		$easter = new DateTime(date('Y') . '-03-21');
		$easter->add(new DateInterval(sprintf('P%dD', easter_days(date('Y')))));

		$easter_friday = clone $easter;
		$easter_friday->modify('-2 days');

		$holidays[$year][$easter_friday->format('m-d')] = 'Good Friday';

		$easter_monday = clone $easter;
		$easter_monday->modify('+1 day');

		$holidays[$year][$easter_monday->format('m-d')] = 'Easter Monday';

		$day = strtotime(sprintf('first monday %d-06', $year));
		$holidays[$year][date('m-d', $day)] = 'Queens Birthday';

		$day = strtotime(sprintf('fourth monday %d-10', $year));
		$holidays[$year][date('m-d', $day)] = 'Labour Day';

		$day = strtotime(sprintf('first monday %d-01-29', $year));
		$holidays[$year][date('m-d', $day)] = 'Auckland Anniversary';

		return $holidays[$year];
	}

	static public function getFRPublicHolidays($year)
	{
		static $holidays = [];

		if (isset($holidays[$year])) {
			return $holidays[$year];
		}

		$holidays[$year] = self::FR_HOLIDAYS;

		// Jours variables
		$easter = new DateTime(date('Y') . '-03-21');
		$easter->add(new DateInterval(sprintf('P%dD', easter_days(date('Y')))));

		$a = clone $easter;
		$a->modify('+39 days'); // Jeudi de l'ascension

		$holidays[$year][$a->format('m-d')] = 'Jeudi de l\'ascension';

		$a = clone $easter;
		$a->modify('+50 days'); // lundi de pentecôte

		$holidays[$year][$a->format('m-d')] = 'Lundi de pentecôte';

		$a = clone $easter;
		$a->modify('+1 day'); // lundi de pâques

		$holidays[$year][$a->format('m-d')] = 'Lundi de pâques';

		return $holidays[$year];
	}

	static public function isPublicHoliday(DateTime $date)
	{
		$fr = self::getFRPublicHolidays($date->format('Y'));

		if (isset($fr[$date->format('m-d')]))
		{
			return $fr[$date->format('m-d')];
		}

		//FIXME: use user country for holidays
		return false;

		$nz = self::getNZPublicHolidays($date->format('Y'));

		if (isset($nz[$date->format('m-d')]))
		{
			return $nz[$date->format('m-d')];
		}

		return false;
	}

	static public function getLocalObservance($m, $d): ?string
	{
		$m = (int)$m;
		$d = (int)$d;

		return self::WORLD_CELEBRATIONS[$m][$d] ?? null;
	}

	static public function getUniqueColor(string $str): int
	{
		$nb = 0;
		for ($i = 0; $i < strlen($str); $i++)
		{
			$nb += ord($str[$i]);
		}

		$hue = $nb % 361;
		return (int) $hue;
	}

	/**
	 * Return a list of days inside a given month
	 */
	static public function getMonth(?int $year = null, ?int $month = null)
	{
		if (is_null($year) && is_null($month)) {
			$year = date('Y');
			$month = date('m');
		}

		$days = [];
		$s = strtotime($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01');
		$firstDayOfMonth = date('N', $s);

		if ($firstDayOfMonth > 1)
		{
			$firstDay = $s - (($firstDayOfMonth - 1) * 3600 * 24);
			$lastDayOfPreviousMonth = date('t', $firstDay);

			for ($d = date('j', $firstDay); $d <= $lastDayOfPreviousMonth; $d++)
			{
				$days[] = DateTime::createFromFormat('!Y-m-d', date('Y-m-', $firstDay) . str_pad($d, 2, '0', STR_PAD_LEFT));
			}
		}

		$nbDaysInMonth = date('t', $s);

		for ($d = 1; $d <= $nbDaysInMonth; $d++)
		{
			$days[] = DateTime::createFromFormat('!Y-m-d', date('Y-m-', $s) . str_pad($d, 2, '0', STR_PAD_LEFT));
		}

		$e = strtotime($year . '-' . $month . '-' . $nbDaysInMonth);
		$lastDayOfMonth = date('N', $e);

		if ($lastDayOfMonth < 7)
		{
			$lastDay = $e + ((7 - $lastDayOfMonth) * 3600 * 24);

			for ($d = 1; $d <= (7 - $lastDayOfMonth); $d++)
			{
				$days[] = DateTime::createFromFormat('!Y-m-d', date('Y-m-', $lastDay) . str_pad($d, 2, '0', STR_PAD_LEFT));
			}
		}

		return $days;
	}
}
