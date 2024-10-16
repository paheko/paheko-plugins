<?php

namespace Paheko\Plugin\Taima;

use Paheko\Plugin\Taima\Entities\Entry;
use Paheko\Plugin\Taima\Entities\Task;

use Paheko\Entities\Signal;
use Paheko\Entities\Accounting\Transaction;
use Paheko\Entities\Accounting\Line;
use Paheko\Entities\Accounting\Year;
use Paheko\Accounting\Accounts;
use Paheko\Accounting\Transactions;

use Paheko\CSV_Custom;
use Paheko\DB;
use Paheko\DynamicList;
use Paheko\Entity;
use Paheko\Plugins;
use Paheko\Utils;
use Paheko\UserException;
use Paheko\Users\DynamicFields;
use Paheko\Users\Session;
use Paheko\UserTemplate\CommonFunctions;

use KD2\DB\EntityManager as EM;

use KD2\DB\Date;

class Tracking
{
	const ANIMATED_ICON = '<svg width="%s" viewBox="0 0 22 22" class="taima-icon-%d" style="%s">
			<style>
				svg.taima-icon-%2$d { animation: taima-spinner 5s linear infinite; fill: %s; stroke: %4$s; vertical-align: middle; }
				@keyframes taima-spinner { to {transform: rotate(360deg);} }
			</style>
			<circle cx="11" cy="11" r="10" stroke-width="2" fill="none" />
			<path class="icon-timer-hand" d="M12.8 10.2L11 2l-1.8 8.2-.2.8c0 1 1 2 2 2s2-1 2-2c0-.3 0-.6-.2-.8z" />
		</svg>';

	const FIXED_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="%s" viewBox="0 0 22 22" id="img" fill="none" stroke="currentColor" >
		<circle cx="11" cy="11" r="10" stroke-width="2" />
		<path id="hand" d="M12.8 10.2L11 2l-1.8 8.2-.2.8c0 1 1 2 2 2s2-1 2-2c0-.3 0-.6-.2-.8z" fill="currentColor" />
	</svg>';

	static public function animatedIcon(string $size, string $color = '', string $style = ''): string
	{
		static $i = 1;
		return sprintf(self::ANIMATED_ICON, $size, $i++, $style, $color ?: 'rgb(var(--gSecondColor))');
	}

	static public function fixedIcon(string $size): string
	{
		return sprintf(self::FIXED_ICON, $size);
	}

	static public function getWorkingHours(int $hours = 35): array
	{
		// 1607 hours = numbers of hours worked in a year for a 35 hour week,
		// counting holidays
		// 35*52 = what you would do as simple math
		// 1596 hours = number of hours worked in a year, without the "solidarity day" (+7 hours)
		// or the "rounding" (+4 hours)
		$legal_work_ratio = 1596/(35*52.1429);

		$hours = $hours ?: 35;
		$week = $hours;
		$year = $hours * 44.4;
		$month = $year / 12;

		return compact('hours', 'week', 'year', 'month');
	}

	static public function homeButton(Signal $signal): void
	{
		$url = Plugins::getPrivateURL('taima');
		$user_id = Session::getUserId();
		$running_timers = $user_id ? self::hasRunningTimers($user_id) : false;

		$params = [
			'label' => $running_timers ? 'Suivi : chrono en cours' : 'Suivi du temps',
			'href' => $url,
		];

		if ($running_timers) {
			$params['icon_html'] = self::animatedIcon('100%', 'rgb(var(--gHoverLinkColor))');
		}
		else {
			$params['icon'] = $url . 'icon.svg';
		}

		$signal->setOut('taima', CommonFunctions::linkbutton($params));
	}

	static public function menuItem(Signal $signal): void
	{
		$icon = '';
		$user_id = Session::getUserId();
		$running_timers = $user_id ? self::hasRunningTimers($user_id) : false;

		if ($user_id && $running_timers) {
			$icon = self::animatedIcon(16, '', 'float: right');
		}

		$signal->setOut('plugin_taima', sprintf('<a href="%sp/taima/">Suivi du temps%s</a>', \Paheko\ADMIN_URL, $icon));
	}

	static public function get(int $id)
	{
		return EM::findOneById(Entry::class, $id);
	}

	static public function listUserEntries(Date $day, int $user_id)
	{
		$sql = sprintf('SELECT e.*, t.label AS task_label,
			CASE WHEN e.timer_started IS NOT NULL
				THEN IFNULL(e.duration, 0) + (strftime(\'%%s\', \'now\') - e.timer_started) / 60
				ELSE e.duration
			END AS timer_running
			FROM %s e LEFT JOIN %s t ON t.id = e.task_id WHERE date = ? AND user_id = ? ORDER BY id;', Entry::TABLE, Task::TABLE);
		return DB::getInstance()->get($sql, $day->format('Y-m-d'), $user_id);
	}

	static public function autoStopRunningTimers()
	{
		$max = 13*60+37; // 13h37
		$db = DB::getInstance();
		$db->exec(sprintf('UPDATE %s
			SET timer_started = NULL,
				duration = IFNULL(duration, 0) + %d
			WHERE timer_started IS NOT NULL
				AND (strftime(\'%%s\', \'now\') - timer_started) > %2$d*60;', Entry::TABLE, $max));
	}

	static public function listUserYears(int $user_id): array
	{
		$sql = sprintf('SELECT year, SUM(duration) AS duration, COUNT(id) AS entries
			FROM %s
			WHERE user_id = ?
			GROUP BY year
			ORDER BY date DESC;', Entry::TABLE);
		return DB::getInstance()->get($sql, $user_id);
	}

	static public function listUserMonths(int $user_id, int $year): array
	{
		$sql = sprintf('SELECT year, SUM(duration) AS duration, COUNT(id) AS entries, date
			FROM %s
			WHERE user_id = ? AND year = ?
			GROUP BY strftime(\'%%Y%%m\', date)
			ORDER BY date DESC;', Entry::TABLE);
		return DB::getInstance()->get($sql, $user_id, $year);
	}

	static public function listUserWeeks(int $user_id, int $year)
	{
		$sql = sprintf('SELECT year, week, SUM(duration) AS duration, COUNT(id) AS entries,
			date(date, \'weekday 0\', \'-6 day\') AS first,
			date(date, \'weekday 0\') AS last
			FROM %s
			WHERE user_id = ? AND year = ?
			GROUP BY year, week
			ORDER BY year DESC, week DESC;', Entry::TABLE);
		return DB::getInstance()->get($sql, $user_id, $year);
	}

	static public function listTasks()
	{
		return DB::getInstance()->getAssoc(sprintf('SELECT id, label FROM %s ORDER BY label COLLATE U_NOCASE;', Task::TABLE));
	}

	static public function getTaskLabel(int $id): ?string
	{
		return DB::getInstance()->firstColumn(sprintf('SELECT label FROM %s WHERE id = ?;', Task::TABLE), $id) ?: null;
	}

	static public function hasRunningTimers(int $user_id): bool
	{
		return DB::getInstance()->test(Entry::TABLE, 'user_id = ? AND timer_started IS NOT NULL', $user_id);
	}

	static public function listUserRunningTimers(?int $user_id, ?Date $except = null): array
	{
		if (!$user_id) {
			return [];
		}

		$params = [$user_id];
		$where = ['user_id = ?', 'timer_started IS NOT NULL'];

		if ($except) {
			$where[] = 'date != ?';
			$params[] = $except->format('Y-m-d');
		}

		$sql = sprintf('SELECT date FROM %s WHERE %s;', Entry::TABLE, implode(' AND ', $where));

		return DB::getInstance()->get($sql, ...$params);
	}

	static public function listUserWeekDays(int $year, int $week, int $user_id)
	{
		$weekdays = [];

		$weekday = new Date;
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

	static public function getList(array $filters = []): DynamicList
	{
		$columns = [
			'user_number' => [
				'label' => 'Numéro de membre',
				'select' => DynamicFields::getNumberFieldSQL('u'),
				'export' => true,
			],
			'user_name' => [
				'label' => 'Nom',
				'select' => DynamicFields::getNameFieldsSQL('u'),
			],
			'task' => [
				'label' => 'Catégorie',
				'select' => 't.label',
				'order' => 't.label COLLATE U_NOCASE %s',
			],
			'notes' => [
				'select' => 'e.notes',
				'label' => 'Notes',
				'export' => true,
			],
			'year' => [
				'label' => 'Année',
				'select' => 'e.year',
				'order' => 'e.year %s, e.week %1$s',
			],
			'week' => [
				'label' => 'Semaine',
				'select' => 'e.week',
				'order' => 'e.year %s, e.week %1$s',
			],
			'date' => [
				'label' => 'Date',
				'select' => 'e.date',
			],
			'duration' => [
				'label' => 'Durée',
				'select' => 'e.duration',
			],
			'value' => [
				'label' => 'Valorisation',
				'select' => 'ROUND((e.duration/60.0 * t.value) / 100, 2)',
				'export' => true,
			],
			'user_id' => [],
			'id' => ['select' => 'e.id'],
		];

		$tables = 'plugin_taima_entries e
			LEFT JOIN plugin_taima_tasks t ON t.id = e.task_id
			LEFT JOIN users u ON u.id = e.user_id';

		$conditions = '1';
		$params = [];

		if (!empty($filters['except'])) {
			$conditions = 'e.user_id IS NULL OR e.user_id != ' . (int)$filters['except'];
		}
		elseif (!empty($filters['id_user'])) {
			$conditions = 'e.user_id = ' . (int)$filters['id_user'];
		}
		elseif (!empty($filters['id_task'])) {
			$conditions = 'e.task_id = ' . (int)$filters['id_task'];
			$columns['task']['export'] = true;
			unset($columns['notes']['export']);
		}

		if (!empty($filters['start']) && ($start = Entity::filterUserDateValue($filters['start']))) {
			$conditions .= ' AND e.date >= :start';
			$params['start'] = $start;
		}

		if (!empty($filters['end']) && ($end = Entity::filterUserDateValue($filters['end']))) {
			$conditions .= ' AND e.date <= :end';
			$params['end'] = $end;
		}

		$list = new DynamicList($columns, $tables, $conditions);
		$list->setParameters($params);
		$list->orderBy('date', true);

		$list->setExportCallback(function (&$row) {
			$row->date = \DateTime::createFromFormat('!Y-m-d', $row->date);
		});

		return $list;
	}

	static public function listPerInterval(string $period = 'week', string $grouping = 'task', array $filters = []): DynamicList
	{
		$columns = [];
		$conditions = '1';
		$order = 'period';
		$desc = true;
		$params = [];
		$tables = 'plugin_taima_entries e
			LEFT JOIN plugin_taima_tasks t ON t.id = e.task_id
			LEFT JOIN users u ON u.id = e.user_id';

		if ($period === 'week') {
			$columns['period'] = [
				'label' => 'Semaine',
				'select' => 'e.year || e.week',
				'order' => 'e.year %s, e.week %1$s',
			];
			$columns['week'] = ['select' => 'e.week'];
			$columns['year'] = ['select' => 'e.year'];

			$group = 'e.year, e.week';
		}
		elseif ($period === 'year') {
			$columns['period'] = [
				'label' => 'Année',
				'order' => 'e.year %s',
				'select' => 'e.year',
			];

			$group = 'e.year';
		}
		elseif ($period === 'month') {
			$columns['period'] = [
				'label' => 'Mois',
				'order' => 'e.date %s',
				'select' => 'strftime(\'%Y%m\', e.date)',
			];
			$columns['date'] = ['select' => 'e.date'];

			$group = 'e.year, strftime(\'%m\', e.date)';
		}
		elseif ($period === 'accounting') {
			$columns['period'] = [
				'label' => 'Exercice',
				'order' => 'y.start_date %s',
				'select' => 'y.label',
			];

			$group = 'y.id';
			$tables .= ' INNER JOIN acc_years AS y ON e.date >= y.start_date AND e.date <= y.end_date';
		}

		if ($grouping === 'user') {
			$columns['group'] = [
				'label' => 'Membre',
				'select' => DynamicFields::getNameFieldsSQL('u'),
				'order' => '"period" %s, "group" COLLATE U_NOCASE %1$s',
			];
			$columns['user_id'] = ['select' => 'e.user_id'];

			$group .= ', e.user_id';
		}
		else {
			$columns['group'] = [
				'label' => 'Tâche',
				'select' => 't.label',
				'order' => '"period" %s, "group" COLLATE U_NOCASE %1$s',
			];
			$columns['task_id'] = ['select' => 'e.task_id'];

			$group .= ', e.task_id';
		}

		$columns['duration'] = [
			'label' => 'Temps cumulé',
			'select' => 'SUM(e.duration)',
			'order' => '"period" %s, SUM(e.duration) %1$s',
		];

		$hours = self::getWorkingHours();

		$columns['etp'] = [
			'label' => sprintf('Équivalent temps plein %dh', $hours['hours']),
			'select' => sprintf('ROUND(SUM(e.duration) / %d.0, 2)', ($hours[$period] ?? $hours['year']) * 60),
			'order' => null,
		];

		if (!empty($filters['start']) && ($start = Entity::filterUserDateValue($filters['start']))) {
			$conditions .= ' AND e.date >= :start';
			$params['start'] = $start;
		}

		if (!empty($filters['end']) && ($end = Entity::filterUserDateValue($filters['end']))) {
			$conditions .= ' AND e.date <= :end';
			$params['end'] = $end;
		}

		$list = new DynamicList($columns, $tables, $conditions);
		$list->groupBy($group);
		$list->orderBy($order, $desc);
		$list->setParameters($params);
		$list->setPageSize(null);

		$current = null;
		$total = 0;
		$total_etp = 0;

		$list->setModifier(function (&$row) use (&$current, &$total, &$total_etp) {
			if ($row->period !== $current) {
				if ($current !== null) {
					yield ['group' => 'total', 'duration' => $total, 'etp' => $total_etp];
				}

				$current = $row->period;
				$total = 0;
				$total_etp = 0;
				$row->header = true;
			}

			$total += $row->duration;
			$total_etp += $row->etp;
		});

		$list->setFinalGenerator(function () use (&$total, &$total_etp) {
			if ($total) {
				yield ['group' => 'total', 'duration' => $total, 'etp' => $total_etp];
			}
		});

		return $list;
	}

	static public function getFinancialReport(Year $year, Date $start, Date $end): DynamicList
	{
		$columns = [
			'label' => [
				'label' => 'Catégorie',
				'select' => 't.label',
				'order' => 't.label COLLATE U_NOCASE %s',
			],
			'hours' => [
				'label' => 'Nombre d\'heures',
				'select' => 'SUM(e.duration) / 60',
			],
			'people' => [
				'label' => 'Nombre de membres',
				'select' => 'COUNT(DISTINCT e.user_id)',
			],
			'value' => [
				'label' => 'Valorisation horaire',
				'select' => 't.value',
			],
			'total' => [
				'label' => 'Valorisation totale',
				'select' => 'SUM(e.duration) / 60 * t.value',
			],
			'account_label' => [
				'label' => 'Compte',
				'select' => 'a.label',
			],
			'id_account' => [
				'select' => 'a.id',
			],
			'account_code' => [
				'select' => 'a.code',
			],
			'id_task' => [
				'select' => 't.id',
			],
		];

		$tables = 'plugin_taima_entries e
			INNER JOIN plugin_taima_tasks t ON t.id = e.task_id
			LEFT JOIN acc_accounts a ON a.code = t.account';

		$conditions = 'a.id_chart = :id_chart
			AND t.value IS NOT NULL
			AND t.account IS NOT NULL
			AND e.date >= :start
			AND e.date <= :end';

		$list = new DynamicList($columns, $tables, $conditions);
		$list->setParameter('id_chart', $year->id_chart);
		$list->setParameters(compact('start', 'end'));
		$list->groupBy('t.id');
		$list->setPageSize(null);
		$list->orderBy('label', false);

		return $list;
	}

	static public function createReport(Year $year, Date $start, Date $end, int $id_creator): Transaction
	{
		$date = new Date;

		if ($date > $year->end_date) {
			$date = clone $year->end_date;
		}
		elseif ($date < $year->start_date) {
			$date = clone $year->start_date;
		}

		$id_account = (new Accounts($year->id_chart))->getIdFromCode('875');

		if (!$id_account) {
			throw new UserException('Le compte 875 n\'existe pas au plan comptable, merci de le créer');
		}

		$t = Transactions::create([
			'date' => $date,
			'label' => 'Valorisation du bénévolat',
			'notes' => 'Écriture créée par Tāima, extension de suivi du temps',
			'type' => Transaction::TYPE_ADVANCED,
			'id_year' => $year->id(),
			'reference' => 'VALORISATION-TAIMA',
		]);

		$t->id_creator = $id_creator;

		$report = self::getFinancialReport($year, $start, $end);

		foreach ($report->iterate() as $row) {
			if (!$row->id_account) {
				continue;
			}

			$line = new Line;
			$line->debit = $row->total;
			$line->id_account = $row->id_account;
			$line->label = sprintf('%s (%d heures à %s / h)', $row->label, $row->hours, Utils::money_format($row->value));

			$t->addLine($line);
		}

		$sum = $t->getLinesDebitSum();

		if (!$sum) {
			throw new UserException('Rien ne peut être valorisé : peut-être que des codes de compte sont invalides ?');
		}

		$line = new Line;
		$line->credit = $sum;
		$line->id_account = $id_account;
		$line->label = 'Temps bénévole';
		$t->addLine($line);

		return $t;
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

	static public function findImportCategories(CSV_Custom $csv, array $tasks): array
	{
		$categories = [];

		foreach ($csv->iterate() as $row) {
			if (isset($row->task)) {
				$categories[$row->task] = null;
			}
		}

		foreach ($categories as $clabel => &$match) {
			foreach ($tasks as $id => $label) {
				if (strnatcasecmp($clabel, $label) === 0) {
					$match = $id;
				}
			}
		}

		unset($match);

		return $categories;
	}

	static public function saveImport(CSV_Custom $csv, array $categories): void
	{
		$db = DB::getInstance();
		$db->begin();

		foreach (self::createImport($csv, $categories) as $e) {
			$e->save();
		}

		$db->commit();
	}

	static public function createImport(CSV_Custom $csv, array $categories): \Generator
	{
		$id_field = DynamicFields::getNameFieldsSQL();
		$db = DB::getInstance();

		foreach ($csv->iterate() as $i => $row) {
			$e = new Entry;
			$e->setDateString($row->date);

			if (isset($row->duration_hours)) {
				$e->setDuration($row->duration_hours);
			}
			elseif (isset($row->duration)) {
				$e->set('duration', (int)$row->duration);
			}
			else {
				throw new UserException('Aucune durée n\'est indiquée');
			}

			if (array_key_exists($row->task, $categories)) {
				$e->set('task_id', (int)$categories[$row->task]);
			}

			$id = null;
			$name = null;

			if (isset($row->name, $row->surname)) {
				$a = trim($row->name . ' ' . $row->surname);
				$b = trim($row->surname . ' ' . $row->name);
				$name = $b;
				$id = $db->firstColumn(sprintf('SELECT id FROM users WHERE %s = ? COLLATE U_NOCASE OR %1$s = ? COLLATE U_NOCASE;', $id_field), $a, $b);
			}

			if (isset($row->fullname)) {
				$name = trim($row->fullname);
				$id = $db->firstColumn(sprintf('SELECT id FROM users WHERE %s = ? COLLATE U_NOCASE;', $id_field), $name);
			}

			$e->set('user_id', $id ?: null);

			$notes = [];

			if (!$e->user_id && $name) {
				$notes[] = $name;
			}

			if (!empty($row->title)) {
				$notes[] = trim($row->title);
			}

			if (!empty($row->notes)) {
				$notes[] = trim($row->notes);
			}

			$e->set('notes', implode("\n", $notes) ?: null);
			yield $i => $e;
		}
	}
}
