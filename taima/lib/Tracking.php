<?php

namespace Garradin\Plugin\Taima;

use Garradin\Plugin\Taima\Entities\Entry;
use Garradin\Plugin\Taima\Entities\Task;

use Garradin\DB;
use KD2\DB\EntityManager as EM;

use DateTime;

class Tracking
{
	static public function get(int $id)
	{
		return EM::findOneById(Entry::class, $id);
	}

	static public function listUserEntries(DateTime $day, int $user_id)
	{
		$sql = sprintf('SELECT e.*, t.label AS task_label,
			CASE WHEN e.timer_started
				THEN e.duration + (strftime(\'%%s\') - e.timer_started) / 60
				ELSE e.duration
			END AS timer_running
			FROM %s e LEFT JOIN %s t ON t.id = e.task_id WHERE date = ? AND user_id = ? ORDER BY id;', Entry::TABLE, Task::TABLE);
		return DB::getInstance()->get($sql, $day->format('Y-m-d'), $user_id);
	}

	static public function listWeeks(int $user_id)
	{
		$sql = sprintf('SELECT year, week, SUM(duration) AS hours FROM %s WHERE user_id = ? GROUP BY year, week ORDER BY year, week;', Entry::TABLE);
		return DB::getInstance()->get($sql, $user_id);
	}

	static public function listTasks()
	{
		return DB::getInstance()->getAssoc(sprintf('SELECT id, label FROM %s ORDER BY label COLLATE NOCASE;', Task::TABLE));
	}

	static public function getWeekDays(int $year, int $week, int $user_id)
	{
		$weekdays = [];

		$weekday = new DateTime;
		$weekday->setISODate($year, $week);

		$db = DB::getInstance();

		$sql = sprintf('SELECT strftime(\'%%w\', date) - 1 AS weekday, SUM(duration) AS total, SUM(timer_started) > 0 AS timers
			FROM %s WHERE year = ? AND week = ? AND user_id = ?
			GROUP BY weekday ORDER BY weekday;', Entry::TABLE);

		$filled_days = $db->getGrouped($sql, [$year, $week, $user_id]);

		// SQLite has Sunday as first day of week
		if (isset($filled_days[-1])) {
			$filled_days[6] = $filled_days[-1];
		}

		for ($i = 0; $i < 7; $i++) {
			$weekdays[] = (object) [
				'day'     => clone $weekday,
				'minutes' => array_key_exists($i, $filled_days) ? $filled_days[$i]->total : 0,
				'timers'  => array_key_exists($i, $filled_days) ? $filled_days[$i]->timers : 0,
				'duration' => array_key_exists($i, $filled_days) ? $filled_days[$i]->total : 0,
			];

			$weekday->modify('+1 day');
		}

		return $weekdays;
	}

	static public function formatMinutes(int $minutes)
	{
		$hours = floor($minutes / 60);
		$minutes -= $hours * 60;

		return sprintf('%d:%02d', $hours, $minutes);
	}
}