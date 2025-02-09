<?php

namespace Paheko\Plugin\PIM\Entities;

use Paheko\Plugin\PIM\ChangesTracker;
use Paheko\Plugin\PIM\Events;
use Paheko\Plugin\PIM\PIM;
use Paheko\Entity;
use Paheko\UserException;
use DateTime;

use Sabre\VObject;

class Event extends Entity
{
	const TABLE = 'plugin_pim_events';

	protected ?int $id = null;
	protected int $id_user;
	protected ?int $id_category;
	protected string $uri;
	protected string $title;
	protected DateTime $start;
	protected DateTime $end;
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
		$this->assert($this->end >= $this->start, 'La date de fin ne peut se situer avant la date de début');
	}

	public function importForm(?array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['start']) && isset($source['start_time'])) {
			$source['start'] = $source['start'] . ' ' . $source['start_time'];
		}

		if (isset($source['end']) && isset($source['end_time'])) {
			$source['end'] = $source['end'] . ' ' . $source['end_time'];
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
			$this->start->setTime(0, 0, 0);
			$this->end->setTime(0, 0, 0);
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
		return $this->end->format('Ymd') > $this->start->format('Ymd');
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

	public function populateFromQueryString(Events $events, array $qs)
	{
		$tz = $qs['tz'] ?? null;

		if (!empty($tz)
			&& in_array($tz, \DateTimeZone::listIdentifiers(), true)) {
		}
		else {
			$tz = $events->getDefaultTimezone();
		}

		$this->set('timezone', $tz);
		$tz = new \DateTimezone($tz);

		if (!empty($qs['start'])) {
			$start = $this->filterUserDateValue($qs['start']);
			$end = $this->filterUserDateValue($qs['end']);
		}

		$start ??= new \DateTime;
		$end ??= new \DateTime;

		$title = $qs['title'] ?? '';
		$location = null;

		// Find location between parenthesis in title
		if (preg_match('/\b\((.+)\)\b/', $title, $match)) {
			$location = trim($match[1]) ?: null;
			$title = trim(str_replace($match[0], '', $title));
		}

		$category_id = null;

		if (!empty($qs['category'])) {
			$category_id = (int) $qs['category'];
		}
		// Recherche du nom de catégorie dans le titre
		elseif (preg_match('/\b#(.+)\b/', $title, $match)) {
			foreach ($events->listCategories() as $cat) {
				if (!strcasecmp($match[1], $cat->title)) {
					$category_id = $cat->id;
					$title = trim(str_replace($match[0], '', $title));
					break;
				}
			}
		}

		if (!$category_id) {
			$category_id = $events->getDefaultCategory();
		}

		if ($category_id) {
			$category = $events->getCategory($category_id);

			if (!$category) {
				throw new UserException('Invalid or unknown category');
			}
		}

		$start->setTimezone($tz);
		$end->setTimezone($tz);

		$title = $this->setTimeFromTitle($title, $start, $end);
		$all_day = false;
		$reminder = 0;

		if ($start->format('Ymd') === $end->format('Ymd')
			&& $start->format('Hi') === '0000') {
			$all_day = true;
		}
		elseif ($start->format('Hi') === '0000'
			&& $end->format('Hi') === '0000') {
			$all_day = true;
		}

		if (!$all_day) {
			$reminder = $category->default_reminder ?? 15;
		}

		$category = $category->id ?? null;
		$this->import(compact('all_day', 'start', 'end', 'reminder', 'title', 'category'));
	}

	protected function setTimeFromTitle(string $str, DateTime $start, DateTime $end): string
	{
		$str = trim($str);

		if (!preg_match('!^(\d+)\s*[h:.](?:\s*(\d+))?(?:\s*-\s*(\d+)[h:.](?:\s*(\d+))?)?\s+!i', $str, $match)) {
			return $str;
		}

		$start_h = (int) $match[1];
		$start_m = (int) ($match[2] ?? 0);
		$end_h = $start_h+1;
		$end_m = 0;

		if ($end->format('Ymd') !== $start->format('Ymd')) {
			$end_h = 0;
		}

		if (!empty($match[3])) {
			$end_h = (int) $match[3];
			$end_m = (int) ($match[4] ?? 0);
		}

		$str = substr($str, strlen($match[0]));

		$start->setTime($start_h, $start_m, 0, 0);
		$end->setTime($end_h, $end_m, 0, 0);
		return $str;
	}

	public function exportVEventArray(): array
	{
		$vevent = [
			'SUMMARY'       => $this->title,
			'DESCRIPTION'   => $this->desc,
			'LOCATION'      => $this->location,
			//RRULE // FIXME
			'UID'           => $this->uri,
			'LAST-MODIFIED' => $this->updated,
		];

		if ($this->all_day) {
			$vevent['DTSTART;VALUE=DATE'] = $this->start->format('Ymd');
			$end = clone $this->end;
			$end->modify('+1 day');
			$vevent['DTEND;VALUE=DATE'] = $end->format('Ymd');
		}
		else {
			$vevent['DTSTART'] = $this->start;
			$vevent['DTEND'] = $this->end;
		}

		if ($this->reminder) {
			$vevent['VALARM'] = [
				'TRIGGER'     => sprintf('-PT%dM', $this->reminder),
				'ACTION'      => 'DISPLAY',
				'DESCRIPTION' => $this->title,
			];
		}

		return $vevent;
	}

	public function exportVEvent(): string
	{
		PIM::enableDependencies();

		$obj = new VObject\Component\VCalendar($this->exportVEventArray());
		return $obj->serialize();
	}

	public function importVEvent($obj)
	{
		if (is_string($obj)) {
			PIM::enableDependencies();
			$obj = VObject\Reader::read($obj)->VEVENT;
		}

		$reminder = 0;

		if (!empty($obj->VALARM)) {
			sscanf($obj->VALARM->TRIGGER, '-PT%dM', $reminder);
		}

		$start = $obj->DTSTART->getDateTime();
		$end = $obj->DTEND->getDateTime();

		// In VEVENT, when event is for a full day, the end date is the next day
		if (!$obj->DTSTART->hasTime()) {
			$all_day = true;
			$start->setTime(0, 0, 0);
			$end->setTime(0, 0, 0);
			$end = $end->modify('-1 day');
		}
		else {
			$all_day = false;
		}

		if ($start > $end) {
			$end = clone $start;
			$end = $end->modify('+1 hour');
		}

		$this->import([
			'title'    => (string) $obj->SUMMARY,
			'start'    => $start,
			'end'      => $end,
			'desc'     => (string) $obj->DESCRIPTION,
			'reminder' => (int) $reminder,
			'all_day'  => (int) $all_day,
			'location' => (string) $obj->LOCATION,
			'timezone' => (string) $start->getTimezone()->getName(),
		]);
	}
}
