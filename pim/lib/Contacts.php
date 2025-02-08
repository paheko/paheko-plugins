<?php

namespace Paheko\Plugin\PIM;

use Paheko\Plugin\PIM\Entities\Contact;
use Paheko\DynamicList;
use DateTime;
use KD2\DB\EntityManager as EM;

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
			'photo' => [
				'label' => 'Photo',
				'order' => null,
			],
			'first_name' => [
				'label' => 'Prénom',
				'order' => 'first_name || \' \' || last_name COLLATE U_NOCASE %s',
			],
			'last_name' => [
				'label' => 'Nom',
				'order' => 'last_name || \' \' || first_name COLLATE U_NOCASE %s',
			],
			'title' => [
				'label' => 'Contexte',
				'order' => 'title || first_name || \' \' || last_name COLLATE U_NOCASE %s',
			],
			'id' => [],
			'uri' => [],
		];

		$list = new DynamicList($columns, Contact::TABLE, 'archived = ' . intval($archived));
		$list->orderBy('first_name', false);
		$list->setModifier(function (&$row) {
			$row->photo = $row->photo ? WWW_URL . $row->photo . '?150px' : WWW_URL . 'user/avatar/contact_' . sha1($row->uri);
		});
		return $list;
	}

	public function exportAll(bool $archived = false): void
	{
		header('Content-Type: text/vcard; charset=utf-8', true);
		header('Content-Disposition: download; filename="export.vcf"', true);

		foreach ($this->listAll($archived) as $contact) {
			echo $contact->getVCard();
		}
	}
}
