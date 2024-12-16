<?php

namespace Paheko\Plugin\PIM\Entities;

use Paheko\Plugin\PIM\ChangesTracker;

use Paheko\Entity;
use KD2\DB\Date;
use DateTime;

class Contact extends Entity
{
	const TABLE = 'plugin_pim_contacts';

	protected ?int $id = null;
	protected int $id_user;
	protected string $uri;
	protected string $first_name;
	protected ?string $last_name;
	protected ?string $title;
	protected ?string $phone;
	protected ?string $email;
	protected ?Date $birthday;
	protected ?string $photo;
	protected ?string $raw;
	protected DateTime $updated;
	protected bool $archived = false;

	public function save(bool $selfcheck = true): bool
	{
		$exists = $this->exists();

		if (!$exists) {
			$this->set('uri', md5(random_bytes(16)));
		}

		$r = parent::save($selfcheck);

		ChangeTracker::record($this->id_user, 'contact', $this->uri, $exists ? ChangeTracker::MODIFIED : ChangeTracker::ADDED);
		return $r;
	}

	public function delete(): bool
	{
		$id = $this->id();
		$r = parent::delete();
		ChangeTracker::record($this->id_user, 'contact', $this->uri, ChangeTracker::DELETED);
		return $r;
	}

	public function setPhoto(array $file): void
	{
		if (empty($file['tmp_name']) || !empty($file['error']) || empty($file['size'])) {
			return;
		}

		$i = new Image;
		$i->openFromPath($file['tmp_name']);
		$i->cropResize(256, 256);
		$i->jpeg_quality = 75;
		$this->set('photo', $i->output('jpeg', true));
	}

}
