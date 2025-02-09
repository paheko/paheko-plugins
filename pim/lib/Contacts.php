<?php

namespace Paheko\Plugin\PIM;

use Paheko\Plugin\PIM\Entities\Contact;
use Paheko\DynamicList;
use DateTime;
use KD2\DB\EntityManager as EM;
use Sabre\VObject;

use const Paheko\WWW_URL;

class Contacts
{
	protected int $id_user;

	public function __construct(int $id_user)
	{
		$this->id_user = $id_user;
	}

	public function get(int $id): ?Contact
	{
		return EM::findOneById(Contact::class, $id);
	}

	public function getFromURI(string $uri): ?Contact
	{
		return EM::findOne(Contact::class, 'SELECT * FROM @TABLE WHERE uri = ?;', $uri);
	}

	public function create(): Contact
	{
		$contact = new Contact;
		$contact->id_user = $this->id_user;
		return $contact;
	}

	public function getUpcomingBirthdays(int $days = 15): array
	{
		$db = DB::getInstance();

		$start = new Date;
		$end = new Date;
		$end->modify(sprintf('+%d days', $days));

		return $this->getBirthdaysForPeriod($start, $end);
	}

	public function getBirthdaysForPeriod(DateTime $start, DateTime $end): array
	{
		$sql = 'SELECT * FROM @TABLE
			WHERE id_user = ?
				AND archived = 0
				AND birthday IS NOT NULL
				AND birthday >= ?
				AND birthday <= ?
			ORDER BY birthday ASC;';

		$out = [];

		foreach (EM::getInstance(Contact::class)->iterate($sql, $this->id_user, $start, $end) as $row) {
			$out[$row->birthday->format('Y-m-d')] = $row;
		}

		return $out;
	}

	public function listAll(bool $archived = false): array
	{
		return EM::getInstance(Contact::class)->all('SELECT * FROM @TABLE WHERE archived = ? ORDER BY first_name || \' \' || last_name COLLATE U_NOCASE;', $archived);
	}

	public function getList(bool $archived = false): DynamicList
	{
		$columns = [
			'has_photo' => [
				'label' => 'Photo',
				'order' => null,
				'select' => 'p.id IS NOT NULL'
			],
			'first_name' => [
				'label' => 'Prénom',
				'select' => 'c.first_name',
				'order' => 'c.first_name || \' \' || c.last_name COLLATE U_NOCASE %s',
			],
			'last_name' => [
				'label' => 'Nom',
				'select' => 'c.last_name',
				'order' => 'c.last_name || \' \' || c.first_name COLLATE U_NOCASE %s',
			],
			'title' => [
				'label' => 'Contexte',
				'select' => 'c.title',
				'order' => 'c.title || c.first_name || \' \' || c.last_name COLLATE U_NOCASE %s',
			],
			'id' => ['select' => 'c.id'],
			'uri' => ['select' => 'c.uri'],
			'updated' => ['select' => 'strftime(\'%s\', c.updated)'],
		];

		$tables = Contact::TABLE . ' AS c LEFT JOIN plugin_pim_contacts_photos p ON p.id = c.id';

		$list = new DynamicList($columns, $tables, 'archived = ' . intval($archived));
		$list->orderBy('first_name', false);
		$list->setPageSize(null);
		$list->setModifier(function (&$row) {
			if ($row->has_photo) {
				$row->photo = sprintf('details.php?id=%d&return=photo&u=%d', $row->id, $row->updated);
			}
			else {
				$row->photo = WWW_URL . 'user/avatar/contact_' . sha1($row->uri);
			}
		});
		return $list;
	}

	public function exportAll(bool $archived = false): void
	{
		header('Content-Type: text/vcard; charset=utf-8', true);
		header('Content-Disposition: download; filename="export.vcf"', true);

		foreach ($this->listAll($archived) as $contact) {
			echo $contact->exportVCard();
		}
	}

	public function import(string $data, bool $archived = false): void
	{
		$v = new VObject\Splitter\VCard($data);

		while ($item = $v->getNext()) {
			$contact = $this->create();
			$contact->importVCard($item);
			$contact->archived = $archived;
			$contact->save();
		}
	}

	public function importFile(string $path, bool $archived = false): void
	{
		$this->import(file_get_contents($path), $archived);
	}
}
