<?php

namespace Paheko\Plugin\PIM\Entities;

use Paheko\Plugin\PIM\ChangesTracker;

use Paheko\Entity;
use Paheko\Entities\Files\File;
use Paheko\Files\Files;
use KD2\DB\Date;
use KD2\Graphics\Image;
use DateTime;

use const Paheko\WWW_URL;

class Contact extends Entity
{
	const TABLE = 'plugin_pim_contacts';

	protected ?int $id = null;
	protected int $id_user;
	protected string $uri;
	protected string $first_name;
	protected ?string $last_name;
	protected ?string $title;
	protected ?string $mobile_phone;
	protected ?string $phone;
	protected ?string $address;
	protected ?string $email;
	protected ?string $web;
	protected ?string $notes;
	protected ?Date $birthday;
	protected ?string $photo = null;
	protected ?string $raw = null;
	protected DateTime $updated;
	protected bool $archived = false;

	public function save(bool $selfcheck = true): bool
	{
		$exists = $this->exists();

		if (!$exists) {
			$this->set('uri', md5(random_bytes(16)));
		}

		if ($this->isModified()) {
			$this->set('updated', new \DateTime);
		}

		$r = parent::save($selfcheck);

		ChangesTracker::record($this->id_user, 'contact', $this->uri, $exists ? ChangesTracker::MODIFIED : ChangesTracker::ADDED);
		return $r;
	}

	public function delete(): bool
	{
		$id = $this->id();
		$this->deletePhoto();
		$r = parent::delete();
		ChangesTracker::record($this->id_user, 'contact', $this->uri, ChangesTracker::DELETED);
		return $r;
	}

	public function deletePhoto(): void
	{
		$photo = $this->getPhotoFile();

		if (!$photo) {
			return;
		}

		$photo->delete();
	}

	public function uploadPhoto(string $root, array $file): void
	{
		if (empty($file['tmp_name']) || !empty($file['error']) || empty($file['size'])) {
			throw new UserException('Fichier invalide');
		}

		$path = $root . '/contacts/' . sha1(random_bytes(16)) . '.webp';

		try {
			$i = new Image;
			$i->openFromPath($file['tmp_name']);
			$i->cropResize(256, 256);
		}
		catch (\UnexpectedValueException $e) {
			throw new UserException('Cet format d\'image n\'est pas supporté.', 0, $e);
		}

		$f = Files::createFromString($path, $i->output('webp', true));

		// Delete previous file
		$this->deletePhoto();

		$this->set('photo', $f->path);
	}

	public function getPhotoFile(): ?File
	{
		if (!$this->photo) {
			return null;
		}

		return Files::get($this->photo);
	}

	public function getPhotoURL(bool $small = false): ?string
	{
		if (!$this->photo) {
			return null;
		}

		$url = WWW_URL . $this->photo;

		if ($small) {
			$url .= '?150px';
		}

		return $url;
	}

	public function importForm(?array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['archived_present'])) {
			$source['archived'] = !empty($source['archived']);
		}

		parent::importForm($source);
	}

	public function getFullName(): string
	{
		return trim($this->first_name . '  ' . $this->last_name);
	}

	public function getAge(): ?int
	{
		if (!$this->birthday) {
			return null;
		}

		$now = new DateTime;
		return $this->birthday->diff($now)->y;
	}

	public function getMapURL(): string
	{
		return 'https://www.openstreetmap.org/search?query=' . rawurlencode($this->address);
	}
}
