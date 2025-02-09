<?php

namespace Paheko\Plugin\PIM;

use DateTime;
use DateInterval;

class Calendar
{
	const FR_SAINTS = [
		1   =>  array(1 => 'Jour de l\'an', 'Basile', 'Geneviève', 'Odilon', 'Edouard',
			'Mélaine', 'Raymond', 'Lucien Felix', 'Alix', 'Guillaume', 'Paulin', 'Tatiana',
			'Yvette', 'Nina', 'Rémi', 'Marcel', 'Roseline', 'Prisca', 'Marius', 'Sébastien',
			'Agnès', 'Vincent', 'Barnard', 'François', 'Manuel, Brittany', 'Paule',
			'Angèle', 'Valère', 'Gildas', 'Martine', 'Marcelle'),
		2   =>  array(1 => 'Ella', 'Théophane', 'Blaise', 'Véronique', 'Agathe', 'Gaston',
			'Eugènie', 'Jacqueline', 'Apolline', 'Arnaud', 'Notre-Dame de Lourdes', 'Félix',
			'Béatrice', 'Valentin', 'Claude', 'Julienne', 'Alexis', 'Bernadette', 'Gabin',
			'Aimée', 'Pierre, Damien', 'Isabelle', 'Lazare', 'Modeste', 'Roméo', 'Nestor',
			'Honorine', 'Romain'),
		3   =>  array(1 => 'Jonathan', 'Charles', 'Guénolé', 'Casimir', 'Olivia', 'Colette',
			'Félicité', 'Journée de la Femme', 'Françoise', 'Vivien', 'Rosine', 'Justine',
			'Rodrigue', 'Mathilde', 'Louise', 'Bénédicte', 'Patrick', 'Cyrille', 'Joseph',
			'Herbert', 'Clémence', 'Léa', 'Victorien', 'Aldemar', 'Humbert', 'Lara', 'Habib',
			'Gontran', 'Gladys', 'Amédée', 'Benjamin'),
		4   =>  array(1 => 'Hugues', 'Sandrine', 'Richard', 'Isidore', 'Irène', 'Marcelin',
			'Clotaire', 'Julie', 'Gautier', 'Fulbert', 'Stanislas', 'Jules', 'Ida', 'Maxime',
			'Paterne', 'Benoît-Joseph', 'Anicet', 'Parfait', 'Emma', 'Odette', 'Anselme',
			'Alexandre', 'Georges', 'Fidèle', 'Marc', 'Alida', 'Zita', 'Valérie', 'Ava, Catherine',
			'Robert'),
		5   =>  array(1 => 'Fête du Travail', 'Boris', 'Philippe', 'Sylvain', 'Judith', 'Prudence',
			'Gisèle', 'Désiré', 'Pacôme', 'Solange', 'Estelle', 'Achille', 'Rolande',
			'Matthias', 'Denise', 'Honoré', 'Pascal', 'Eric', 'Yves', 'Bernardin', 'Constantin',
			'Emile', 'Didier', 'Donatien', 'Sophie', 'Bérenger', 'Augustin', 'Germain', 'Aymar',
			'Ferdinand', 'Visitation'),
		6   =>  array(1 => 'Justin', 'Blandine', 'Kévin', 'Clotilde', 'Igor', 'Norbert', 'Gilbert',
			'Médard', 'Diane', 'Landry', 'Barnabé', 'Guy', 'St Antoine de Padoue', 'Elisée',
			'Germaine', 'St Jean-François Régis', 'Hervé', 'Léonce', 'Romuald', 'Silvère',
			'Rodolphe', 'Aaron', 'Audrey', 'Jean-Baptiste', 'Eléonore', 'Anthelme', 'Fernand',
			'Irénée', 'Pierre, Paul', 'Martial'),
		7   =>  array(1 => 'Thierry', 'Martinien', 'Thomas', 'Florent, Eliane', 'Antoine',
			'Mariette, Nolwenn', 'Raoul', 'Thibaut', 'Amandine', 'Ulrich', 'Benoît', 'Olivier',
			'Henri Enzo', 'Camille - Fête Nationale', 'Donald Wladimir', 'Carmen Elvire',
			'Carole, Charlotte, Caroline', 'Frédéric', 'Arsène', 'Marine', 'Victor',
			'Madeleine, Margaux', 'Brigitte', 'Christine Ségolène', 'Jacques, James',
			'Anne, Nancy', 'Nathalie', 'Samson', 'Marthe', 'Juliette', 'Germain'),
		8   =>  array(1 => 'Alphonse', 'Alexandrine', 'Lydie', 'Jean-Marie', 'Abel', 'Octavien',
			'Gaëtan', 'Dominique', 'Amour', 'Laurent, Laura', 'Clara', 'Clarisse',
			'Hippolyte, Philomène', 'Evrard, Maximilien', 'Alfred, Myriam, Marion, Marie',
			'Armel', 'Hyacinthe', 'Hélène, Laetitia', 'Eudes', 'Bernard', 'Christophe, Noémie',
			'Fabrice', 'Rose', 'Barthélémy, Nathanaël', 'Louis, Clovis, Ludovic', 'Natacha',
			'Monique', 'Linda', 'Sabine, Sabrina', 'Sacha', 'Aristide'),
		9   =>  array(1 => 'Gilles', 'Ingrid', 'Grégory', 'Rosalie Moïse', 'Raïssa', 'Bertrand',
			'Régine', 'Adrien', 'Alain', 'Inès', 'Adelphe', 'Apollinaire', 'Aimé', 'Materne',
			'Lola, Roland', 'Edith', 'Renaud', 'Nadège Nadia', 'Emilie', 'Davy', 'Matthieu',
			'Maurice', 'Constant', 'Thècle', 'Hermann', 'Damien', 'Vincent', 'Venceslas',
			'Michel', 'Jérôme'),
		10  =>  array(1 => 'Thérèse', 'Léger', 'Gérard, Blanche', 'Fanny, Oriane', 'Fleur',
			'Bruno', 'Serge', 'Pélagie', 'Denis, Sybille', 'Ghislain, Virgile', 'Firmin',
			'Wilfried', 'Géraud', 'Juste, Céleste', 'Aurélie', 'Edwige', 'Baudouin', 'Luc',
			'René', 'Adeline', 'Céline', 'Elodie', 'St Jean de Capistran', 'Florentin',
			'Enguerran, Doria', 'Dimitri', 'Emeline', 'Simon, Jude', 'Narcisse', 'Bienvenue',
			'Quentin'),
		11  =>  array(1 => 'Toussaint', 'Jour des défunts', 'Hubert', 'Aymeric, Karl, Charles',
			'Sylvie', 'Léonard', 'Carine', 'Geoffroy', 'Théodore', 'Léon', 'Martin',
			'Christian', 'Brice', 'Sidonie', 'Arthur, Albert', 'Marguerite', 'Elisabeth, Elsa',
			'Aude', 'Tanguy', 'Edmond, Edmée', 'Présentation de Marie', 'Cécile', 'Clément',
			'Flora', 'Catherine', 'Delphine', 'Astrid Séverine', 'St Jacques de la Marche',
			'Saturnin', 'André'),
		12  =>  array(1 => 'Florence', 'Viviane', 'François-Xavier', 'Barbara', 'Gérald', 'Nicolas',
			'Ambroise', 'Frida Elfried', 'Fourier', 'Romaric', 'Daniel', 'Chantal', 'Lucie / ACAB',
			'Odile', 'Ninon', 'Alice', 'Gaël', 'Gatien', 'Urbain', 'Théophile', 'St Pierre C.',
			'Xavière', 'Armand', 'Adèle', 'Noël', 'Etienne', 'Jean',
			'Gaspard - Saints Innocents', 'David', 'Roger', 'Saint Sylvestre'),
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

	static public function getLocalSaint($m, $d)
	{
		$m = (int)$m;
		$d = (int)$d;

		$saints = self::FR_SAINTS;

		return isset($saints[$m][$d]) ? $saints[$m][$d] : null;
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
