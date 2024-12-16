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
			ChangesTracker::record($this->id_user, 'event', $this->uri, ChangeTracker::DELETED);
			$this->set('uri', md5(random_bytes(16)));
			$exists = false;
		}

		$r = parent::save($selfcheck);

		ChangesTracker::record($this->id_user, 'event', $this->uri, $exists ? ChangeTracker::MODIFIED : ChangeTracker::ADDED);
		return $r;
	}

	public function delete(): bool
	{
		$id = $this->id();
		$r = parent::delete();
		ChangesTracker::record($this->id_user, 'event', $this->uri, ChangeTracker::DELETED);
		return $r;
	}

	public function isRunning(): bool
	{
		return $this->date_end->format('Ymd') > $this->date->format('Ymd');
	}

	public function getClass(): string
	{
		$class = '';

		if ($this->isRunning()) {
			$class .= ' running';
		}

		if ($this->all_day) {
			$class .= ' all_day';
		}

		return $class;
	}
}
