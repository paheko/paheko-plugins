<?php

namespace Paheko\Plugin\PIM;

use Paheko\Plugin\PIM\Entities\Event;
use Paheko\Plugin\PIM\Entities\Event_Category;
use Paheko\DB;
use Paheko\Utils;
use Paheko\ValidationException;
use DateTime;
use DateTimezone;
use Sabre\VObject;

use KD2\DB\EntityManager as EM;

class Events
{
	const TEXT = '75%, 30%';
	const RUNNING = '50%, 90%';
	const ALL_DAY = '50%, 75%';

	protected int $id_user;

	public function __construct(int $id_user)
	{
		$this->id_user = $id_user;
	}

	static public function hsl($h, $sl)
	{
		return sprintf('hsl(%d, %s)', $h, $sl);
	}

	// Renvoie la TZ la plus commune sur les 10 derniers événements
	public function getDefaultTimezone(): string
	{
		$tz = DB::getInstance()->firstColumn('SELECT timezone,
			COUNT(*) AS nb FROM (SELECT timezone FROM plugin_pim_events WHERE id_user = ? ORDER BY updated DESC LIMIT 10)
			GROUP BY timezone
			ORDER BY nb DESC LIMIT 1;', $this->id_user);

		if (!$tz) {
			$tz = date_default_timezone_get();
		}

		if (!$tz) {
			$tz = 'Europe/Paris';
		}

		return $tz;
	}

	public function getDefaultCategory(): ?int
	{
		$db = DB::getInstance();
		return $db->firstColumn('SELECT id FROM plugin_pim_events_categories WHERE id_user = ? ORDER BY is_default DESC LIMIT 1;', $this->id_user) ?: null;
	}

	public function setDefaultCategory(int $id): void
	{
		$db = DB::getInstance();

		$id = (int)$id;
		$db->update('plugin_pim_events_categories', ['is_default' => 0], sprintf('id != %d AND id_user = %d', $id, $this->id_user));
		$db->update('plugin_pim_events_categories', ['is_default' => 1], sprintf('id = %d AND id_user = %d', $id, $this->id_user));
	}

	public function setDefaultCategoryIfMissing(): void
	{
		if ($this->getDefaultCategory()) {
			return;
		}

		$db = DB::getInstance();
		$id = $db->firstColumn('SELECT id FROM plugin_pim_events_categories WHERE id_user = ? LIMIT 1;', $this->id_user);

		if ($id) {
			$events->setDefaultCategory($id);
		}
		else {
			$c = $this->createCategory();
			$c->title = 'Personnel';
			$c->color = 120;
			$c->default_reminder = 15;
			$c->is_default = true;
			$c->save();
		}
	}

	public function create(): Event
	{
		$event = new Event;
		$event->id_user = $this->id_user;
		$event->id_category = $this->getDefaultCategory();
		return $event;
	}

	public function createCategory(): Event_Category
	{
		$c = new Event_Category;
		$c->id_user = $this->id_user;
		return $c;
	}

	public function get(int $id): ?Event
	{
		return EM::findOneById(Event::class, $id);
	}

	public function getFromURI(string $uri): ?Event
	{
		return EM::findOne(Event::class, 'SELECT * FROM @TABLE WHERE uri = ?;', $uri);
	}

	public function listForCategory(int $id): array
	{
		return EM::getInstance(Event::class)->all('SELECT * FROM @TABLE WHERE id_user = ? AND id_category = ?;', $this->id_user, $id);
	}

	public function getEventsForPeriod(DateTime $start, DateTime $end): array
	{
		$start->setTime(0, 0, 0);
		$end->setTime(23, 59, 59);

		$sql = 'SELECT * FROM @TABLE
			WHERE id_user = :id_user
			AND (
				(start >= :start AND start <= :end)
				OR (end >= :start AND end <= :end)
			)
			ORDER BY start, end;';

		$days = [];
		$id_user = $this->id_user;
		$em = EM::getInstance(Event::class);

		foreach ($em->iterate($sql, compact('id_user', 'start', 'end')) as $row) {
			if ($row->start < $start) {
				$s = clone $start;
			}
			else {
				$s = clone $row->start;
			}

			if ($row->end > $end) {
				$e = $end;
			}
			else {
				$e = $row->end;
			}

			// Add events to array of days
			while ($s->format('Ymd') <= $e->format('Ymd')) {
				$key = $s->format('Y-m-d');

				$days[$key] ??= [];
				$days[$key][] = $row;

				$s->modify('+1 day');
			}

			unset($s);
		}

		foreach ($days as $key => &$events) {
			usort($events, function($a, $b)
			{
				if ($a->start == $b->start)
					return 0;

				return ($a->start < $b->start) ? -1 : 1;
			});
		}

		unset($events);


		return $days;
	}

	public function getCalendar(int $y, int $m)
	{
		$period = Calendar::getMonth($y, $m);
		$events = $this->getEventsForPeriod(reset($period), end($period));
		$contacts = new Contacts($this->id_user);
		$birthdays = $contacts->getBirthdaysForPeriod(reset($period), end($period));
		$today = date('Y-m-d');
		$colors = DB::getInstance()->getAssoc('SELECT id, color FROM plugin_pim_events_categories WHERE id_user = ?;', $this->id_user);

		$rows = [];
		$week = [];

		foreach ($period as $day) {
			$item = new \stdClass;
			$item->date_ymd = $day->format('Y-m-d');
			$item->date = $day;
			$item->same_month = $item->date->format('m') == $m;
			$item->holiday = Calendar::isPublicHoliday($item->date);
			$item->today = $today === $item->date_ymd;
			$item->observance = Calendar::getLocalObservance($day->format('m'), $day->format('d'));
			$item->class = '';

			if ($item->holiday) {
				$item->class .= ' holiday';
			}

			if ($item->today) {
				$item->class .= ' today';
			}

			if (!$item->same_month) {
				$item->class .= ' other_month';
			}

			if ($item->observance) {
				$item->class .= ' observance';
			}

			$item->events = [];

			foreach ($events[$item->date_ymd] ?? [] as $e) {
				$running = $e->isRunning();
				$starts = '';
				$ends = '';

				if ($running && $e->start->format('Hi') !== '0000' && $e->start->format('Y-m-d') === $item->date_ymd) {
					$starts = $e->start->format('H:i');
				}
				elseif (!$running && !$e->all_day && $e->end->format('Y-m-d') === $item->date_ymd) {
					$starts = $e->start->format('H:i');
				}

				if ($running && $e->end->format('Hi') != '0000' && $e->end->format('Y-m-d') == $item->date_ymd) {
					$ends = $e->end->format('H:i');
				}

				$item->events[] = [
					'class'  => $e->getClass(),
					'style'  => isset($colors[$e->id_category]) ? sprintf('--hue: %d', $colors[$e->id_category]) : '',
					'url'    => 'edit.php?id=' . $e->id,
					'target' => '_dialog',
					'title'  => $e->title,
					'starts' => $starts,
					'ends'   => $ends,
				];
			}

			foreach ($birthdays[$day->format('m-d')] ?? [] as $contact) {
				$item->events[] = [
					'class' => 'birthday',
					'url' => 'contacts/details.php?id=' . $contact->id,
					'title' => $contact->getFullName(),
					'subtitle' => sprintf('(%d ans)', $contact->getAge($item->date)),
					'target' => '_dialog',
				];
			}

			$week[] = $item;

			if (count($week) === 7) {
				$rows[] = $week;
				$week = [];
			}
		}

		return $rows;
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

	public function listCategories(): array
	{
		return EM::getInstance(Event_Category::class)->all('SELECT * FROM @TABLE WHERE id_user = ? ORDER BY title COLLATE U_NOCASE;', $this->id_user);
	}

	public function getCategory(int $id): ?Event_Category
	{
		return EM::findOne(Event_Category::class, 'SELECT * FROM @TABLE WHERE id_user = ? AND id = ? ORDER BY title COLLATE U_NOCASE;', $this->id_user, $id);
	}

	public function listChangesForCategory($category, $timestamp)
	{
		$db = DB::getInstance();

		return $db->get('SELECT c.uri, c.type FROM ' . $this->changes_table . ' AS c 
			INNER JOIN agenda AS a ON a.uri = c.uri
			WHERE c.timestamp >= ? AND a.category = ?
			ORDER BY c.timestamp DESC;', $timestamp, $category);

	}

	public function importFile(string $path)
	{
		return $this->import(file_get_contents($path));
	}

	public function import(string $data)
	{
		if (!preg_match('/^BEGIN:VCALENDAR/', $data)) {
			throw new ValidationException('Invalid file: not a VCalendar');
		}

		// Handle multiple VCalendar in the same file
		$data = preg_split("/\r?\nEND:VCALENDAR\r?\nBEGIN:VCALENDAR\r?\n/", $data);

		$db = DB::getInstance();
		$db->begin();

		foreach ($data as $i => $item) {
			$item = trim($item);
			$item = preg_replace('/^BEGIN:VCALENDAR\s*|\s*END:VCALENDAR$/', '', $item);
			$item = "BEGIN:VCALENDAR\r\n" . $item . "\r\nEND:VCALENDAR";

			$v = VObject\Reader::read($item);

			if (isset($v->{'X-WR-NAME'})) {
				$cat = $this->createCategory();
				$cat->title = $v->{'X-WR-NAME'}->getValue();

				if (isset($v->{'X-APPLE-CALENDAR-COLOR'})) {
					$hex = $v->{'X-APPLE-CALENDAR-COLOR'}->getValue();
					$color = Utils::rgbToHsv($hex)[0] ?? 0;
					$cat->color = intval($color);
				}

				$cat->save();
				$cat_id = $cat->id;
			}
			else {
				$cat_id = $this->getDefaultCategory();
			}

			if (!$cat_id) {
				throw new ValidationException('Aucune catégorie par défaut n\'a été définie');
			}

			foreach ($v->VEVENT as $vevent) {
				$event = $this->create();
				$event->id_category = $cat_id;
				$event->importVEvent($vevent);
				$event->save();
			}
		}

		$db->commit();
	}

	protected function export(Event_Category $cat): string
	{
		$vcal = new VObject\Component\VCalendar([
			'X-WR-NAME' => $cat->title,
			'X-APPLE-CALENDAR-COLOR' => Utils::hsl2rgb($cat->color, 50, 75),
		]);

		$em = EM::getInstance(Event::class);

		foreach ($em->iterate('SELECT * FROM @TABLE WHERE id_category = ?;', $cat->id) as $event) {
			$vcal->add('VEVENT', $event->exportVEventArray());
		}

		return $vcal->serialize();
	}

	public function exportCategory(int $id): void
	{
		$cat = $this->getCategory($id);

		if (!$cat) {
			return;
		}

		header('Content-Type: text/calendar; charset=utf-8');
		header(sprintf('Content-Disposition: download; filename="%s.ics"', $cat->title), true);

		echo $this->export($cat);
	}

	public function exportAll(): void
	{
		header('Content-Type: text/calendar; charset=utf-8');
		header(sprintf('Content-Disposition: download; filename="%s.ics"', 'export_all'), true);

		foreach ($this->listCategories() as $cat) {
			echo $this->export($cat);
		}
	}
}
