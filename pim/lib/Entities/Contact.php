<?php

namespace Paheko\Plugin\PIM\Entities;

use Paheko\Plugin\PIM\ChangesTracker;

use Paheko\DB;
use Paheko\Entity;
use Paheko\Utils;
use KD2\DB\Date;
use KD2\Graphics\Image;
use DateTime;

use const Paheko\WWW_URL;
use Sabre\VObject;

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
	protected ?string $raw = null;
	protected DateTime $updated;
	protected bool $archived = false;

	protected ?string $_photo = null;
	protected bool $_has_photo;

	public function selfCheck(): void
	{
		parent::selfCheck();

		$this->assert(strlen($this->uri) && strlen($this->uri) < 255, 'Invalid URI');
		$this->assert(is_null($this->raw) || strlen($this->raw) <= 1024*50, 'Raw event data is too large');
	}

	public function save(bool $selfcheck = true): bool
	{
		$exists = $this->exists();

		if (!$exists && !isset($this->uri)) {
			$this->set('uri', md5(random_bytes(16)));
		}

		if ($this->isModified() || $this->_photo) {
			$this->set('updated', new \DateTime);
		}

		$r = parent::save($selfcheck);

		if ($this->_photo) {
			$db = DB::getInstance();
			$db->preparedQuery('REPLACE INTO plugin_pim_contacts_photos (id, photo) VALUES (?, zeroblob(?));', $this->id(), strlen($this->_photo));
			$blob = $db->openBlob('plugin_pim_contacts_photos', 'photo', $this->id(), 'main', \SQLITE3_OPEN_READWRITE);

			fwrite($blob, $this->_photo);
			fclose($blob);

			$this->_photo = null;
		}

		ChangesTracker::record($this->id_user, 'contact', $this->uri, $exists ? ChangesTracker::MODIFIED : ChangesTracker::ADDED);
		return $r;
	}

	public function delete(): bool
	{
		$id = $this->id();
		$r = parent::delete();
		ChangesTracker::record($this->id_user, 'contact', $this->uri, ChangesTracker::DELETED);
		return $r;
	}

	public function deletePhoto(): void
	{
		DB::getInstance()->delete('plugin_pim_contacts_photos', 'id = ?', $this->id());
		$this->_has_photo = null;
	}

	public function uploadPhoto(array $file): void
	{
		if (empty($file['tmp_name']) || !empty($file['error']) || empty($file['size'])) {
			throw new UserException('Fichier invalide');
		}

		try {
			$i = new Image;
			$i->openFromPath($file['tmp_name']);
			$i->cropResize(200, 200);
		}
		catch (\UnexpectedValueException $e) {
			throw new UserException('Cet format d\'image n\'est pas supporté.', 0, $e);
		}

		$this->_photo = $i->output('webp', true);
		$this->_has_photo = true;
	}

	public function hasPhoto(): bool
	{
		$this->_has_photo ??= DB::getInstance()->test('plugin_pim_contacts_photos', 'id = ?', $this->id());
		return $this->_has_photo;
	}

	public function servePhoto(): void
	{
		Utils::HTTPCache(null, $this->updated->getTimestamp(), 3600*24*7, true);

		$photo = DB::getInstance()->firstColumn('SELECT photo FROM plugin_pim_contacts_photos WHERE id = ?;', $this->id());
		header('Content-Type: image/webp', true);
		header('Content-Length: ' . strlen($photo), true);

		echo $photo;
	}

	public function getPhotoURL(): ?string
	{
		if (!$this->hasPhoto()) {
			return null;
		}

		return sprintf('./details.php?id=%d&return=photo&u=%s', $this->id(), $this->updated->getTimestamp());
	}

	public function getPhotoHash(): ?string
	{
		if (!$this->exists()) {
			return null;
		}

		return DB::getInstance()->firstColumn('SELECT md5(photo) FROM plugin_pim_contacts_photos WHERE id = ?;', $this->id()) ?: null;
	}

	public function getPhotoBase64(): ?string
	{
		$photo = DB::getInstance()->firstColumn('SELECT photo FROM plugin_pim_contacts_photos WHERE id = ?;', $this->id());

		if (!$photo) {
			return null;
		}

		return 'data:image/webp;base64,' . base64_encode($photo);
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

	public function getAge(?DateTime $date = null): ?int
	{
		if (!$this->birthday) {
			return null;
		}

		$date ??= new DateTime;
		return $this->birthday->diff($date)->y;
	}

	public function getMapURL(): string
	{
		return 'https://www.openstreetmap.org/search?query=' . rawurlencode(str_replace("\n", ', ', $this->address));
	}

	public function exportVCard(): string
	{
		$data = [
			'N'     => [$this->last_name, $this->first_name],
			'NOTE'  => $this->notes,
			'BDAY'  => $this->birthday,
			'PHOTO' => $this->getPhotoBase64(),
			'URL'   => $this->web,
			'EMAIL' => $this->email,
			'UID'   => $this->uri,
			'TITLE' => $this->title,
			'ADR;TYPE=HOME' => $this->address,
			'TEL;TYPE=CELL' => $this->mobile_phone,
			'TEL;TYPE=HOME' => $this->phone,
		];

		$data = array_filter($data);

		// Merge with original VCard
		if ($this->raw) {
			$vcard = VObject\Reader::read($this->raw);

			foreach ($data as $name => $value) {
				$vcard->remove($name);
				$vcard->add($name, $value);
			}
		}
		else {
			$vcard = new VObject\Component\VCard($data);
		}

		$vcard = $vcard->convert(VObject\Document::VCARD30);
		return $vcard->serialize();
	}

	public function importVCard($obj): void
	{
		if (is_string($obj)) {
			$obj = VObject\Reader::read($obj);
		}

		if (!empty($obj->PHOTO)) {
			if (!$this->exists()
				|| $this->getPhotoHash() !== md5($obj->PHOTO->getValue())) {
				try {
					$i = Image::createFromBlob($obj->PHOTO->getValue());
					$i->cropResize(200, 200);
					$this->_photo = $i->output('webp', true);
					$this->_has_photo = true;
					unset($i);
				}
				catch (\UnexpectedValueException $e) {
					// Ignore invalid images
				}
			}
		}

		$name = explode(';', $obj->N->getValue());

		$this->import([
			'last_name'    => $name[0] ?? null,
			'first_name'   => $name[1] ?? null,
			'mobile_phone' => ($tel = $obj->getByType('TEL', 'cell')) ? str_replace('tel:', '', $tel->getValue()) : null,
			'phone'        => ($tel = $obj->getByType('TEL', 'home')) ? str_replace('tel:', '', $tel->getValue()) : null,
			'address'      => $obj->ADR ? trim(str_replace([';', "\r\n", "\n", "\\n", "\,"], "\n", $obj->ADR->getValue())) : null,
			'email'        => $obj->EMAIL ? $obj->EMAIL->getValue() : null,
			'web'          => $obj->URL ? $obj->URL->getValue() : null,
			'birthday'     => $obj->BDAY ? $obj->BDAY->getDateTime() : null,
			'title'        => $obj->TITLE ? $obj->TITLE->getValue() : null,
			'notes'        => $obj->NOTES ? trim(str_replace([';', "\r\n", "\n", "\\n", "\,"], "\n", $obj->NOTES->getValue())) : null,
		]);
	}

	public function etag(): string
	{
		return md5($this->uri . $this->updated->getTimestamp());
	}
}
