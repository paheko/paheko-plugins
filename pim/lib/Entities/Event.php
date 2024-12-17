<?php

namespace Paheko\Plugin\PIM\Entities;

use Paheko\Plugin\PIM\ChangesTracker;
use Paheko\Entity;
use DateTime;

class Event extends Entity
{
	const TABLE = 'plugin_pim_events';

	protected ?int $id = null;
	protected int $id_user;
	protected ?int $id_category;
	protected string $uri;
	protected string $title;
	protected DateTime $date;
	protected DateTime $date_end;
	protected bool $all_day;
	protected string $timezone;
	protected ?string $desc;
	protected ?string $location;
	protected int $reminder = 0;
	protected int $reminder_status = 0;
	protected ?string $raw;
	protected DateTime $updated;

	const TITLE_REPLACE = ['->' => '→', '<-' => '←'];

	const SL_TEXT = '75%, 30%';
	const SL_RUNNING = '50%, 90%';
	const SL_ALL_DAY = '50%, 75%';

	public function selfCheck(): void
	{
		parent::selfCheck();

		$this->assert(isset($this->title) && strlen(trim($this->title)), 'Le titre doit être renseigné.');
		$this->assert($this->date_end >= $this->date, 'La date de fin ne peut se situer avant la date de début');
	}

	public function importForm(?array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['date']) && isset($source['date_time'])) {
			$source['date'] = $source['date'] . ' ' . $source['date_time'];
		}

		if (isset($source['date_end']) && isset($source['date_end_time'])) {
			$source['date_end'] = $source['date_end'] . ' ' . $source['date_end_time'];
		}

		if (isset($source['all_day_present'])) {
			$source['all_day'] = !empty($source['all_day']);
		}

		parent::importForm($source);
	}

	public function save(bool $selfcheck = true): bool
	{
		$exists = $this->exists();

		if (!$exists) {
			$this->set('uri', md5(random_bytes(16)));
		}

		if ($this->all_day) {
			$this->date->setTime(0, 0, 0);
			$this->date_end->setTime(0, 0, 0);
		}

		if ($this->isModified('title')) {
			$this->set('title', strtr($this->title, self::TITLE_REPLACE));
		}

		// Si la catégorie change on déplace de calendrier en fait, affectons un nouveau URI
		if ($this->isModified('id_category')) {
			ChangesTracker::record($this->id_user, 'event', $this->uri, ChangesTracker::DELETED);
			$this->set('uri', md5(random_bytes(16)));
			$exists = false;
		}

		if ($this->isModified()) {
			$this->set('updated', new \DateTime);
		}

		$r = parent::save($selfcheck);

		ChangesTracker::record($this->id_user, 'event', $this->uri, $exists ? ChangesTracker::MODIFIED : ChangesTracker::ADDED);
		return $r;
	}

	public function delete(): bool
	{
		$id = $this->id();
		$r = parent::delete();
		ChangesTracker::record($this->id_user, 'event', $this->uri, ChangesTracker::DELETED);
		return $r;
	}

	public function isRunning(): bool
	{
		return $this->date_end->format('Ymd') > $this->date->format('Ymd');
	}

	public function getClass(): string
	{
		if ($this->isRunning()) {
			$class = 'running';
		}
		elseif ($this->all_day) {
			$class = 'all_day';
		}
		else {
			$class = 'other';
		}

		return $class;
	}

	public function populateFromQueryString(array $qs)
	{
		$tz = Agenda::findTimezoneName($qs['offset'] ?? null);

		if ($tz) {
			$this->set('timezone', $tz);
		}

		$date = DateTime::createFromFormat('Y-m-d', $_GET['start'], new \DateTimezone($tz));
		$date_end = DateTime::createFromFormat('Y-m-d', $_GET['end'], new \DateTimezone($tz));

		if ($title = ($qs['title'] ?? null)) {
			// Find location between parenthesis in title
			if (preg_match('/\b\((.+)\)\b/', $title, $match)) {
				$this->set('location', trim($match[1]) ?: null);
				$title = trim(str_replace($match[0], '', $title));
			}

			$date = !empty($_POST['date']) ? trim($_POST['date']) : $date->format('d/m/Y');
			$date_end = !empty($_POST['date_end']) ? trim($_POST['date_end']) : $date_end->format('d/m/Y');
			$location = null;
			$category = $default_category;

			if (!empty($_POST['category'])) {
				$category = $_POST['category'];
			}
			// Recherche du nom de catégorie dans le titre
			elseif (preg_match('/\b#(.+)\b/', $title, $match)) {
				foreach ($cats as $cat) {
					if (!strcasecmp($match[1], $cat->title)) {
						$category = $cat->id;
						$title = trim(str_replace($match[0], '', $title));
						break;
					}
				}
			}


			$date = $a->getDateFromUserEntry($date, false, $tz);
			$date_end = $a->getDateFromUserEntry($date_end, false, $tz);

			$all_day = ($date == $date_end && $date->format('Hi') == '0000') ? true : false;

			if (list($b, $e) = $a->extractTimeFromTitle($title, $date, $date_end))
			{
				$all_day = false;
				$date->setTime($b['h'], $b['m'], 0);
				$date_end->setTime($e['h'], $e['m'], 0);
			}

			$reminder = ($all_day || $date->format('Hi') == '0000') ? 0 : $cats[$category]->default_reminder;

			$a->add($title, $date, $date_end, $all_day, null, $category, $reminder, $location);
			Utils::redirect('agenda/?y='.$date->format('Y').'&m='.$date->format('m'));
		}
	}
}
