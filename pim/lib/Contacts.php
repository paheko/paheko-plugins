<?php

namespace Paheko\Plugin\PIM;

use Paheko\Plugin\PIM\Entities\Contact;
use DateTime;
use KD2\DB\EntityManager as EM;

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
}
