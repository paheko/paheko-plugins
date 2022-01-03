<?php

namespace Garradin\Plugin\Taima;

use Garradin\Plugin\Taima\Entities\Entry;
use Garradin\Plugin\Taima\Entities\Task;

use Garradin\Config;
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
			CASE WHEN e.timer_started IS NOT NULL
				THEN IFNULL(e.duration, 0) + (strftime(\'%%s\', \'now\') - e.timer_started) / 60
				ELSE e.duration
			END AS timer_running
			FROM %s e LEFT JOIN %s t ON t.id = e.task_id WHERE date = ? AND user_id = ? ORDER BY id;', Entry::TABLE, Task::TABLE);
		return DB::getInstance()->get($sql, $day->format('Y-m-d'), $user_id);
	}

	static public function listWeeks(int $user_id)
	{
		$sql = sprintf('SELECT year, week, SUM(duration) AS duration, COUNT(id) AS entries,
			date(date, \'weekday 0\', \'-6 day\') AS first,
			date(date, \'weekday 0\') AS last
			FROM %s WHERE user_id = ? GROUP BY year, week ORDER BY year, week;', Entry::TABLE);
		return DB::getInstance()->get($sql, $user_id);
	}

	static public function listTasks()
	{
		return DB::getInstance()->getAssoc(sprintf('SELECT id, label FROM %s ORDER BY label COLLATE NOCASE;', Task::TABLE));
	}

	static public function listRunningTimers(DateTime $except, int $user_id)
	{
		return DB::getInstance()->get(sprintf('SELECT date FROM %s
			WHERE date != ? AND user_id = ? AND timer_started IS NOT NULL;', Entry::TABLE), $except->format('Y-m-d'), $user_id);
	}

	static public function getList()
	{
		$identity = Config::getInstance()->get('champ_identite');
		$columns = [
			'task' => [
				'label' => 'Tâche',
				'select' => 't.label',
				'order' => 't.label COLLATE NOCASE AS %s',
			],
			'year' => [
				'label' => 'Année',
				'select' => 'e.year',
			],
			'year' => [
				'label' => 'Semaine',
				'select' => 'e.week',
			],
			'date' => [
				'label' => 'Date',
				'select' => 'e.date',
			],
			'duration' => [
				'label' => 'Durée',
				'select' => 'e.duration',
			],
			'user_name' => [
				'label' => 'Nom',
				'select' => 'm.' . $identity,
			],
		];

		$tables = 'plugin_taima_entries e
			LEFT JOIN plugin_taima_tasks t ON t.id = e.task_id
			INNER JOIN membres m ON m.id = e.user_id';

		$list = new DynamicList($columns, $tables, $conditions);
		$list->orderBy('date', true);
		return $list;
	}

	static public function listPerWeek(string $grouping = 'week', bool $per_user = false)
	{
		if ($grouping == 'week') {
			$group = 'e.year, e.week';
			$order = 'e.year DESC, e.week DESC';
			$criteria = '(e.year || e.week)';
		}
		elseif ($grouping == 'year') {
			$group = 'e.year';
			$order = 'e.year DESC';
			$criteria = 'e.year';
		}
		elseif ($grouping == 'month') {
			$group = 'e.year, strftime(\'%m\', e.date)';
			$order = 'e.year DESC, strftime(\'%m\', e.date) DESC';
			$criteria = 'strftime(\'%Y%m\', e.date)';
		}

		if ($per_user) {
			$group .= ', e.user_id';
		}
		else {
			$group .= ', e.task_id';
		}

		$identity = Config::getInstance()->get('champ_identite');
		$sql = 'SELECT e.*, t.label AS task_label, m.%s AS user_name, SUM(duration) AS duration, %s AS criteria
			FROM plugin_taima_entries e
			LEFT JOIN plugin_taima_tasks t ON t.id = e.task_id
			INNER JOIN membres m ON m.id = e.user_id
			GROUP BY %s
			ORDER BY %s, SUM(duration) DESC;';

		$sql = sprintf($sql, $identity, $criteria, $group, $order);

		$db = DB::getInstance();

		$item = $criteria = null;

		foreach ($db->iterate($sql) as $row) {
			if ($criteria != $row->criteria) {
				if ($item !== null) {
					$total = 0;
					foreach ($item['entries'] as $entry) {
						$total += $entry->duration;
					}

					$item['entries'][] = (object) ['task_label' => 'Total', 'duration' => $total];
					yield $item;
				}

				$criteria = $row->criteria;
				$item = (array)$row;
				$item['entries'] = [];
			}

			$item['entries'][] = $row;
		}

		if ($item !== null) {
			$total = 0;
			foreach ($item['entries'] as $entry) {
				$total += $entry->duration;
			}

			$item['entries'][] = (object) ['task_label' => 'Total', 'duration' => $total];
			yield $item;
		}
	}

	static public function getWeekDays(int $year, int $week, int $user_id)
	{
		$weekdays = [];

		$weekday = new DateTime;
		$weekday->setISODate($year, $week);

		$db = DB::getInstance();

		$sql = sprintf('SELECT strftime(\'%%w\', date) - 1 AS weekday,
			SUM(CASE WHEN timer_started IS NOT NULL
				THEN IFNULL(duration, 0) + (strftime(\'%%s\', \'now\') - timer_started) / 60
				ELSE duration
			END) AS total,
			COUNT(timer_started) AS timers
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

	static public function formatMinutes(?int $minutes): string
	{
		if (!$minutes) {
			return '0:00';
		}

		$hours = floor($minutes / 60);
		$minutes -= $hours * 60;

		return sprintf('%d:%02d', $hours, $minutes);
	}
}