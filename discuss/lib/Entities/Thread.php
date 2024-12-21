<?php

namespace Paheko\Plugin\Discuss\Entities;

use Paheko\Entity;

use DateTime;

class Thread extends Entity
{
	const TABLE = 'plugin_discuss_threads';

	protected ?int $id;
	protected int $id_forum;
	protected string $uri;
	protected string $subject;
	protected DateTime $last_update;
	protected int $status;
	protected int $replies_count;

	const HIDDEN = 0x01 << 1;
	const PINNED = 0x01 << 2;
	const CLOSED = 0x01 << 3;

	public function iterateMessages(): \Generator
	{
		$em = EntityManager::getInstance(Message::class);
		return $em->iterate('SELECT * FROM @TABLE WHERE thread_id = ? ORDER BY date;', $this->id);
	}
}
