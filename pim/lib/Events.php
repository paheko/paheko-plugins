<?php

namespace Paheko\Plugin\PIM;

use Paheko\Plugin\PIM\Entities\Event;
use Paheko\Plugin\PIM\Entities\Event_Category;
use DateTime;
use DateTimezone;
use Sabre\VObject;

class Events
{
	const TEXT = '75%, 30%';
	const RUNNING = '50%, 90%';
	const ALL_DAY = '50%, 75%';

	const TIMEZONES = [
		'Australia/Adelaide',
		'Australia/Hobart',
		'Australia/Melbourne',
		'Australia/Perth',
		'Australia/Sydney',
		'Europe/Paris',
		'Pacific/Auckland',
		'UTC'
	];

	protected int $id_user;

	public function __construct(int $id_user)
	{
		$this->id_user = $id_user;
	}

	static public function hsl($h, $sl)
	{
		return sprintf('hsl(%d, %s)', $h, $sl);
	}

	static public function findTimezoneName($offset = null)
	{
		if (null !== $offset)
		{
			foreach (self::TIMEZONES as $tz)
			{
				$d = new DateTime('now', new DateTimezone($tz));

				if ($d->getOffset() / 60 == $offset)
				{
					return $tz;
				}
			}
		}

		return new date_default_timezone_get();
	}

	// Renvoie la TZ la plus commune sur les 10 derniers événements
	public function getCurrentTimezone(): ?string
	{
		return DB::getInstance()->firstColumn('SELECT timezone,
			COUNT(*) AS nb FROM (SELECT timezone FROM plugin_pim_events WHERE id_user = ? ORDER BY date DESC LIMIT 10)
			GROUP BY timezone
			ORDER BY nb DESC LIMIT 1;', $this->id_user);
	}

	public function getDefaultCategory()
	{
		$db = DB::getInstance();
		return $db->firstColumn('SELECT id FROM plugin_pim_events_categories WHERE id_user = ? is_default = 1 LIMIT 1;', $this->id_user);
	}

	public function setDefaultCategory(int $id): void
	{
		$db = DB::getInstance();

		$id = (int)$id;
		$db->update('plugin_pim_events_categories', ['is_default' => 0], sprintf('id != %d AND id_user = ?', $id, $this->id_user));
		$db->update('plugin_pim_events_categories', ['is_default' => 1], sprintf('id = %d AND id_user = ?', $id, $this->id_user));
	}

	public function get(int $id): ?Event
	{
		return EM::findOneById(Event::class, $id);
	}

	public function listForCategory(int $id): array
	{
		return EM::getInstance()->all('SELECT * FROM @TABLE WHERE id_user = ? AND id_category = ?;', $this->id_user, $id);
	}

	public function getEventsForPeriod(DateTime $start, DateTime $end): array
	{
		$start->setTime(0, 0, 0);
		$end->setTime(23, 59, 59);

		$query = 'SELECT * FROM @TABLE WHERE
			id_user = ?
			AND (
				(date >= :start AND date <= :end)
				OR (date_end >= :start AND date_end <= :end)
			)
			ORDER BY date, date_end;';

		$days = [];
		$id_user = $this->id_user;

		foreach ($em->iterate($sql, compact('id_user', 'start', 'end')) as $row) {
			if ($row->date < $start) {
				$s = clone $start;
			}
			else {
				$s = clone $row->date;
			}

			if ($row->date_end > $end) {
				$e = $end;
			}
			else {
				$e = $row->date_end;
			}

			// Add events to array of days
			while ($s->format('Ymd') <= $e->format('Ymd')) {
				$key = $s->format('Y-m-d');

				if (!isset($days[$key]))
				{
					$days[$key] = array();
				}

				$days[$key][] = $row;

				$s->modify('+1 day');
			}

			unset($s);
		}

		foreach ($days as $key => &$events) {
			usort($events, function($a, $b)
			{
				if ($a->date == $b->date)
					return 0;

				return ($a->date < $b->date) ? -1 : 1;
			});
		}

		unset($events);

		return $days;
	}

	public function getDateFromUserEntry(string $str, bool $all_day = false, ?string $tz = null): DateTime
	{
		$str = trim($str);

		if (!preg_match('!^(\d+)\s*[/.-]\s*(\d+)\s*[/.-]\s*(\d+)(?:\s+(\d+)\s*[h:.]\s*(\d+))?$!', $str, $match)) {
			throw new UserException("Date invalide : ".$str);
		}

		if ((int)$match[3] < 2000) {
			$match[3] = (int)$match[3] + 2000;
		}

		if ($all_day) {
			unset($match[4], $match[5]);
		}

		$date = new DateTime;
		$date->setTimezone(new DateTimezone($tz ?: date_default_timezone_get()));
		$date->setDate($match[3], $match[2], $match[1]);

		if (isset($match[4])) {
			$date->setTime($match[4], $match[5], 0);
		}
		else {
			$date->setTime(0, 0, 0);
		}

		return $date;
	}

	public function extractTimeFromTitle(&$str, DateTime $date, DateTime $date_end)
	{
		$str = trim($str);
		if (!preg_match('!^(\d+)\s*[h:.](?:\s*(\d+))?(?:\s*-\s*(\d+)[h:.](?:\s*(\d+))?)?\s+!i', $str, $match)) {
			return false;
		}

		$begin = array('h' => (int) $match[1], 'm' => 0);
		$end = array('h' => (int) $match[1] + 1, 'm' => 0);

		if ($date_end->format('Ymd') != $date->format('Ymd')) {
			$end['h'] = 0;
		}

		if (!empty($match[2])) {
			$begin['m'] = (int) $match[2];
		}

		if (!empty($match[3])) {
			$end['h'] = (int) $match[3];

			if (!empty($match[4]))
				$end['m'] = (int) $match[4];
		}

		$str = substr($str, strlen($match[0]));

		return array($begin, $end);
	}

	public function listChangesForCategory($category, $timestamp)
	{
		$db = DB::getInstance();

		return $db->get('SELECT c.uri, c.type FROM ' . $this->changes_table . ' AS c 
			INNER JOIN agenda AS a ON a.uri = c.uri
			WHERE c.timestamp >= ? AND a.category = ?
			ORDER BY c.timestamp DESC;', $timestamp, $category);

	}

	public function addFromVCalendar($data, $category, $uri = null)
	{
		$data = $this->unserializeEvent($data);

		$tz = $data->date->getTimezone()->getName();

		if ($tz == 'UTC')
		{
			$tz = $this->getCurrentTimezone();
		}

		return $this->add($data->title, $data->date, $data->date_end, $data->all_day, $data->desc, (int)$category, $data->reminder, $data->location, $uri, $tz);
	}

	public function unserializeEvent($data)
	{
		$obj = VObject\Reader::read($data);
		$reminder = 0;

		if ($obj->VEVENT->VALARM)
		{
			sscanf($obj->VEVENT->VALARM->TRIGGER, '-PT%dM', $reminder);
		}

		$date = $obj->VEVENT->DTSTART->getDateTime();
		$date_end = $obj->VEVENT->DTEND->getDateTime();

		if (!$obj->VEVENT->DTSTART->hasTime())
		{
			$all_day = true;
			$date->setTime(0, 0, 0);
			$date_end = $date_end->modify('-1 day');
			$date_end->setTime(23, 59, 59);
		}
		else
		{
			$all_day = false;
		}

		return (object) [
			'title'    => (string) $obj->VEVENT->SUMMARY,
			'date'     => $date,
			'date_end' => $date_end,
			'desc'     => (string) $obj->VEVENT->DESCRIPTION,
			'reminder' => (int) $reminder,
			'all_day'  => (int) $all_day,
			'location' => $obj->VEVENT->LOCATION,
		];
	}
}
