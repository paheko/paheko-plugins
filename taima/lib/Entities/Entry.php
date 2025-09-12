<?php

namespace Paheko\Plugin\Taima\Entities;

use Paheko\Entity;
use Paheko\Form;
use Paheko\Users\Users;
use Paheko\Utils;

use KD2\DB\Date;

class Entry extends Entity
{
	const TABLE = 'plugin_taima_entries';

	protected int $id;
	protected ?int $user_id;
	protected ?int $task_id = null;
	protected Date $date;
	protected ?string $notes;
	protected ?int $duration;
	protected ?int $timer_started = null;
	protected int $year;
	protected int $week;

	public function selfCheck(): void
	{
		parent::selfCheck();

		$this->assert(!(is_null($this->duration) && is_null($this->timer_started)), 'Duration cannot be NULL if timer is not running');
	}

	public function importForm(?array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['user'])) {
			$source['user_id'] = Form::getSelectorValue($source['user']);
		}

		if (isset($source['date'])) {
			$this->setDateString($source['date']);
			unset($source['date']);
		}

		if (isset($source['duration'])) {
			$this->setDuration($source['duration']);
			unset($source['duration']);
		}

		return parent::importForm($source);
	}

	public function setDate(Date $date)
	{
		$this->set('year', (int) $date->format('o'));
		$this->set('week', (int) $date->format('W'));
		$this->set('date', $date);
	}

	public function setDateString(string $date)
	{
		if (trim($date) === '') {
			return;
		}

		$ts = Utils::parseDateTime($date, Date::class);

		$this->assert($ts !== null, 'Invalid date string: ' . $date);
		$this->setDate($ts);
	}

	public function setDuration(string $duration = null)
	{
		$duration = trim($duration);

		if ($duration === '') {
			$this->set('duration', null);
			$this->start();
			return;
		}

		if (preg_match('/^(\d+)[h:](\d*)$/', $duration, $match)) {
			$minutes = (int) $match[1] * 60 + (int) $match[2];
		}
		elseif (preg_match('/^(\d+)(?:[.,](\d*))?$/', $duration, $match)) {
			$minutes = (int) $match[1] * 60;

			if (!empty($match[2])) {
				$minutes += 60 * ((int) $match[2] / 100);
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

	public function user_name(): ?string
	{
		if (null === $this->user_id) {
			return null;
		}

		return Users::getName($this->user_id);
	}
}
