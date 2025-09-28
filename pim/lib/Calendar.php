<?php

namespace Paheko\Plugin\PIM;

use DateTime;
use DateInterval;

class Calendar
{
	const WORLD_CELEBRATIONS = [
		1 => [
			1 => ['Journée mondiale de la paix', '🕊️'],
			4 => ['Journée mondiale du braille', '⠃⠗⠁⠊⠇⠇⠑'],
			15 => ['Journée de Wikipédia', 'WP'],
			20 => ['Martin Luther King Day', null, 'https://fr.wikipedia.org/wiki/Martin_Luther_King_Day'],
			21 => ['Journée internationale des câlins', '🫂', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_des_c%C3%A2lins'],
			22 => ['Journée de l\'amitié franco-allemande', '🇩🇪', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_franco-allemande'],
			26 => ['Journée internationale des énergies propres', '☀️', 'https://www.un.org/fr/observances/clean-energy-day'],
			27 => ['Journée internationale dédiée à la mémoire des victimes de l\'Holocauste', null, 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_d%C3%A9di%C3%A9e_%C3%A0_la_m%C3%A9moire_des_victimes_de_l%27Holocauste'],
			28 => ['Journée européenne de la protection des données', '🪪','https://fr.wikipedia.org/wiki/Journ%C3%A9e_europ%C3%A9enne_de_la_protection_des_donn%C3%A9es'],
		],
		2 => [
			2 => ['Journée mondiale des zones humides', '🐸', 'https://www.un.org/fr/observances/world-wetlands-day'],
			4 => ['Journée mondiale contre le cancer', '🦀', 'https://www.ligue-cancer.net/journee-mondiale-contre-le-cancer'],
			6 => ['Journée mondiale sans téléphone portable', '📴', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_sans_t%C3%A9l%C3%A9phone_portable'],
			7 => ['Journée mondiale sans téléphone portable', '📴', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_sans_t%C3%A9l%C3%A9phone_portable'],
			8 => ['Journée mondiale sans téléphone portable', '📴', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_sans_t%C3%A9l%C3%A9phone_portable'],
			11 => ['Journée mondiale des malades', '🤒'],
			20 => ['Journée mondiale de la justice sociale', '✊', 'https://www.un.org/fr/observances/social-justice-day'],
			21 => ['Journée internationale de la langue maternelle', '🗣️', 'https://www.un.org/fr/observances/mother-language-day'],
		],
		3 => [
			3 => ['Journée mondiale de la vie sauvage', '🐺', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_de_la_vie_sauvage'],
			5 => ['Journée internationale pour le désarmement et la non-prolifération', '☢️', 'https://www.un.org/fr/observances/disarmament-non-proliferation-awareness-day'],
			8 => ['Journée internationale des droits des femmes', '✊', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_des_femmes'],
			14 => ['Journée de pi', 'π', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_de_pi'],
			15 => ['Journée internationale de lutte contre l\'islamophobie', '🧕', 'https://www.un.org/fr/observances/anti-islamophobia-day'],
			20 => ['Journée internationale du bonheur', '😺', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_du_bonheur'],
			21 => ['Journée internationale pour l\'élimination de la discrimination raciale', null, 'https://www.un.org/fr/observances/end-racism-day'],
			22 => ['Journée mondiale de l\'eau', '💧', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_de_l%27eau'],
			30 => ['Journée internationale du zéro déchet', '🗑️', 'https://www.un.org/fr/observances/zero-waste-day'],
			31 => ['Journée internationale de visibilité transgenre', '🏳️‍⚧️', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_de_visibilit%C3%A9_transgenre']
		],
		4 => [
			2 => ['Journée mondiale de la sensibilisation à l\'autisme', '♾️', 'https://www.un.org/fr/observances/autism-day'],
			7 => ['Journée mondiale de la santé', '🤒', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_de_la_sant%C3%A9'],
			8 => ['Journée internationale des Roms', null, 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_des_Roms'],
			12 => ['Nuit de Youri Gagarine', '👨‍🚀', 'https://fr.wikipedia.org/wiki/Nuit_de_Youri'],
			14 => ['Journée nationale du souvenir de la déportation', null, 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_nationale_du_souvenir_de_la_d%C3%A9portation'],
			15 => ['Steal something from work Day', null, 'https://fr.crimethinc.com/steal-something-from-work-day'],
			22 => ['Journée de la terre', '🌱', 'https://fr.wikipedia.org/wiki/Jour_de_la_Terre'],
			24 => ['Journée de commémoration du génocide arménien', '🇦🇲', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_de_comm%C3%A9moration_du_g%C3%A9nocide_arm%C3%A9nien'],
			28 => ['Journée mondiale contre les accidents du travail', '🦺', 'https://www.un.org/fr/observances/work-safety-day'],
			29 => ['Journée internationale de la danse', '🩰', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_de_la_danse'],
		],
		5 => [
			1 => ['Journée internationale des travailleurs', '👩‍🏭', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_des_travailleurs'],
			3 => ['Journée mondiale de la liberté de la presse', '📰', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_de_la_libert%C3%A9_de_la_presse'],
			6 => ['Journée internationale sans régime', '🎗️', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_sans_r%C3%A9gime'],
			7 => ['Journée mondiale des orphelins du sida', '🧒', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_des_orphelins_du_sida'],
			9 => ['Journée de l\'Europe', '🇪🇺', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_de_l%27Europe_(Union_europ%C3%A9enne)'],
			12 => ['Journée internationale des infirmières', '👩🏾‍⚕️', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_des_infirmi%C3%A8res'],
			17 => ['Journée mondiale de lutte contre l\'homophobie, la transphobie et la biphobie', '👨‍❤️‍💋‍👨', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_contre_l%27homophobie,_la_transphobie_et_la_biphobie'],
			20 => ['Journée mondiale des abeilles', '🐝', 'https://www.un.org/fr/observances/bee-day'],
			21 => ['Journée internationale du thé', '🫖', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_du_th%C3%A9'],
			22 => ['Journée internationale de la biodiversité', '🦗', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_de_la_biodiversit%C3%A9'],
			25 => ['Jour de la serviette', '42', 'https://fr.wikipedia.org/wiki/Jour_de_la_serviette'],
			26 => ['National Sorry Day (Australie)', '🖐🏿', 'https://fr.wikipedia.org/wiki/National_Sorry_Day'],
			30 => ['Journée internationale de la patate', '🥔', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_de_la_pomme_de_terre'],
			31 => ['Journée mondiale sans tabac', '🚭', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_sans_tabac'],
		],
		6 => [
			3 => ['Journée mondiale du vélo', '🚲', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_de_la_bicyclette'],
			5 => ['Journée mondiale de l\'environnement', '🌏', 'https://www.un.org/fr/observances/environment-day'],
			8 => ['Journée mondiale des océans', '🐋', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_de_l%27oc%C3%A9an'],
			12 => ['Journée mondiale contre le travail des enfants', null, 'https://www.un.org/fr/observances/world-day-against-child-labour'],
			14 => ['Journée mondiale du donneur de sang', '🧛‍♀️', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_du_donneur_de_sang'],
			16 => ['Journée internationale de l’enfant africain', null, 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_de_l%27enfant_africain'],
			18 => ['Journée internationale contre les discours haineux', null, 'https://www.un.org/fr/observances/countering-hate-speech'],
			20 => ['Journée mondiale des réfugiés', 'Welcome', 'https://www.un.org/fr/observances/refugee-day'],
			21 => ['Fête de la musique', '🎶', 'https://fr.wikipedia.org/wiki/F%C3%AAte_de_la_musique'],
			26 => ['Journée internationale contre la torture', null, 'https://www.un.org/fr/observances/torture-victims-day'],
			28 => ['Marche des fiertés', '🏳️‍🌈', 'https://fr.wikipedia.org/wiki/Marche_des_fiert%C3%A9s'],
			30 => ['Journée internationale des astéroïdes', '☄️', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_des_ast%C3%A9ro%C3%AFdes'],
		],
		7 => [
			20 => ['Journée internationale de la lune', '🌜', 'https://www.un.org/fr/observances/moon-day'],
			30 => ['Journée internationale de l\'amitié', '👭', 'https://fr.wikipedia.org/wiki/Jour_de_l%27amiti%C3%A9'],
		],
		8 => [
			9 => ['Journée internationale des populations autochtones', null, 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_des_populations_autochtones'],
			12 => ['Journée internationale de la jeunesse', '🛝', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_de_la_jeunesse'],
			13 => ['Journée internationale des gauchers', '🤛🏼', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_des_gauchers'],
			19 => ['Journée mondiale de l\'aide humanitaire', null, 'https://www.who.int/europe/fr/news-room/events/item/2024/08/19/default-calendar/world-humanitarian-day'],
			23 => ['Journée européenne du souvenir', '🪦', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_europ%C3%A9enne_du_souvenir'],
			29 => ['Journée internationale contre les essais nucléaires', '☢️', 'https://www.un.org/fr/observances/end-nuclear-tests-day'],
		],
		9 => [
			1 => ['Journée internationale de l\'alphabétisation', '🔤', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_de_l%27alphab%C3%A9tisation'],
			19 => ['International Talk Like a Pirate Day', '🏴‍☠️', 'https://fr.wikipedia.org/wiki/International_Talk_Like_a_Pirate_Day'],
			21 => ['Journée internationale de la paix', '🕊️', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_de_la_paix'],
			22 => ['Journée sans voiture', '🚵‍♀️', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_sans_voiture'],
			23 => ['Journée de la bisexualité', '🏳️‍⚧️', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_de_la_bisexualit%C3%A9'],
			26 => ['Journée européenne des langues', '🗣️', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_europ%C3%A9enne_des_langues'],
			28 => ['Journée internationale pour le droit à l\'IVG', '📣✊♀️', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_pour_le_droit_%C3%A0_l%27avortement'],
			30 => ['Journée nationale de la vérité et de la réconciliation (Canada)', '👐🏽', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_nationale_de_la_v%C3%A9rit%C3%A9_et_de_la_r%C3%A9conciliation'],
		],
		10 => [
			1 => ['Journée internationale pour les personnes âgées', '👵🏽', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_pour_les_personnes_%C3%A2g%C3%A9es'],
			2 => ['Journée internationale de la non-violence', '🚫💣', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_de_la_non-violence'],
			4 => ['Journée mondiale des animaux', '🐨', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_des_animaux'],
			5 => ['Journée mondiale des enseignants', '👩🏼‍🏫', 'https://www.unesco.org/fr/days/teachers'],
			10 => ['Journée mondiale contre la peine de mort', '🚫💀', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_contre_la_peine_de_mort'],
			11 => ['Journée internationale des droits des filles', '👧🏿', 'https://www.un.org/fr/observances/girl-child-day'],
			16 => ['Journée mondiale de l\'alimentation', '🍜', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_de_l%27alimentation'],
			17 => ['Journée internationale pour l\'élimination de la pauvreté', null, 'https://www.un.org/fr/observances/day-for-eradicating-poverty'],
			19 => ['Journée mondiale des toilettes', '🚽', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_des_toilettes'],
			23 => ['Journée de la Mole', '🧪', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_de_la_Mole'],
			24 => ['Journée des Nations unies', '🇺🇳', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_des_Nations_unies'],
			25 => ['Journée européenne de la justice', '⚖️', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_europ%C3%A9enne_de_la_justice'],
			28 => ['Journée mondiale du cinéma d\'animation', '🎬', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_du_cin%C3%A9ma_d%27animation'],
		],
		11 => [
			1 => ['Journée internationale du végétalisme', '🍆', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_du_v%C3%A9ganisme'],
			2 => ['Journée internationale pour la fin de l\'impunité pour les crimes contre les journalistes', null, 'https://www.un.org/fr/observances/end-impunity-crimes-against-journalists'],
			5 => ['Journée mondiale de sensibilisation aux tsunamis', '🌊', 'https://www.un.org/fr/observances/tsunami-awareness-day'],
			10 => ['Journée mondiale de la science pour la paix', '🔬', 'https://www.un.org/fr/observances/world-science-day'],
			16 => ['Journée internationale de la tolérance', null, 'https://www.unesco.org/fr/days/tolerance'],
			17 => ['Journée mondial du souvenir des victimes d\'accidents de la route', '🚓', 'https://www.un.org/fr/observances/road-traffic-victims-day'],
			20 => ['Journée internationale des droits de l\'enfant', '👧🏻', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_des_droits_de_l%27enfant'],
			25 => ['Journée internationale pour l\'élimination de la violence à l\'égard des femmes', '🚫', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_pour_l%27%C3%A9limination_de_la_violence_%C3%A0_l%27%C3%A9gard_des_femmes'],
			26 => ['Journée mondiale du transport durable', '🚆', 'https://www.un.org/fr/observances/sustainable-transport-day'],
			29 => ['Journée internationale de solidarité avec le peuple palestinien', '🇵🇸', 'https://www.un.org/fr/observances/International-day-of-solidarity-with-the-palestinian-people'],
		],
		12 => [
			1 => ['Journée mondiale de lutte contre le sida', '❤️', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_de_lutte_contre_le_sida'],
			2 => ['Journée internationale de l\'abolition de l\'esclavage', null, 'https://www.un.org/fr/observances/slavery-abolition-day'],
			10 => ['Journée des droits humains', null, 'https://www.un.org/fr/observances/human-rights-day'],
			11 => ['Journée internationale de la montagne', '⛰️', 'https://www.un.org/fr/observances/mountain-day'],
			18 => ['Journée internationale des migrants', 'Welcome', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_internationale_des_migrants'],
			20 => ['Journée de la solidarité entre humains', '✊🏾', 'https://www.un.org/fr/observances/human-solidarity-day'],
			21 => ['Journée mondiale de l\'orgasme', '🌋', 'https://fr.wikipedia.org/wiki/Journ%C3%A9e_mondiale_de_l%27orgasme'],
			27 => ['Journée internationale de préparation aux épidémies', '😷', 'https://www.un.org/fr/observances/epidemic-preparedness-day'],
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

	static public function getLocalObservance($m, $d): ?array
	{
		$m = (int)$m;
		$d = (int)$d;

		if (!isset(self::WORLD_CELEBRATIONS[$m][$d])) {
			return null;
		}

		$day = self::WORLD_CELEBRATIONS[$m][$d];

		if (is_array($day)) {
			return ['label' => $day[0], 'emoji' => $day[1] ?? '✊', 'url' => $day[2] ?? null];
		}

		return [
			'label' => $day,
			'emoji' => '✊',
			'url' => null,
		];
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
