<?php

namespace Paheko\Plugin\Taima\Entities;

use Paheko\Entity;

use KD2\DB\Date;

class Entry extends Entity
{
	const TABLE = 'plugin_taima_entries';

	protected int $id;
	protected ?int $user_id;
	protected ?int $task_id;
	protected Date $date;
	protected ?string $notes;
	protected ?int $duration;
	protected ?int $timer_started;
	protected int $year;
	protected int $week;

	public function selfCheck(): void
	{
		parent::selfCheck();

		if (!$this->task_id) {
			$this->task_id = null;
		}

		$this->assert(!(is_null($this->duration) && is_null($this->timer_started)), 'Duration cannot be NULL if timer is not running');
	}

	public function setDate(Date $date)
	{
		$this->set('year', (int) $date->format('o'));
		$this->set('week', (int) $date->format('W'));
		$this->set('date', $date);
	}

	public function setDateString(string $date)
	{
		$this->setDate($this->filterUserValue('date', $date, 'date'));
	}

	public function setDuration(string $duration = null)
	{
		$duration = trim($duration);

		if ($duration === '') {
			$this->set('duration', null);
			$this->start();
			return;
		}

		if (preg_match('/^(\d+)[h:](\d+)$/', $duration, $match)) {
			$minutes = $match[1] * 60 + $match[2];
		}
		elseif (preg_match('/^(\d+)(?:[.,](\d+))?$/', $duration, $match)) {
			$minutes = $match[1] * 60;

			if (!empty($match[2])) {
				$minutes += 60 * ($match[2] / 100);
			}
		}
		else {
			throw new \InvalidArgumentException('Invalid duration: ' . $duration);
		}

		$this->set('timer_started', null);
		$this->set('duration', (int) $minutes);
	}

	public function start(): void
	{
		if ($this->timer_started) {
			return;
		}

		$this->set('timer_started', time());
	}

	public function stop(): void {
		if (!$this->timer_started) {
			return;
		}

		$this->set('duration', intval($this->duration + ceil((time() - $this->timer_started) / 60)));
		$this->set('timer_started', null);
	}
}
