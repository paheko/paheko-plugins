<?php

namespace Paheko\Plugin\Discuss\Entities;

use Paheko\Entity;

use DateTime;
use KD2\DB\EntityManager;

class Message extends Entity
{
	const TABLE = 'plugin_discuss_messages';

	protected ?int $id;
	protected int $id_forum;
	protected int $id_parent;
	protected ?int $parent_id;
	protected int $level;
	protected string $message_id;
	protected DateTime $date;
	protected ?int $user_id;
	protected ?string $from_name;
	protected ?string $from_email;
	protected string $content;
	protected bool $has_attachments;
	protected ?array $deleted_attachments;
	protected bool $is_censored;
	protected bool $is_from_moderator;
	protected bool $is_internal;

	const HIDDEN = 0x01 << 1;

	public function listAttachments(): array
	{
		// Don't load content in memory
		return EM::getInstance(Attachment::class)->get('SELECT id, message_id, name, mime, NULL as content FROM @TABLE WHERE message_id = ?;',
			$this->id());
	}

	public function getAttachment(int $id): ?Attachment
	{
		return EM::getInstance(Attachment::class)->findOne('SELECT id, message_id, name, mime, NULL as content FROM @TABLE WHERE id = ?;',
			$id);
	}


	static public function extractName(string $from): string
	{
		if (preg_match('/["\'](.+?)[\'"]/', $from, $match))
			return $match[1];
		elseif (preg_match('/\\((.+?)\\)/', $from, $match))
			return $match[1];
		elseif (($pos = strpos($from, '<')) > 0)
			return trim(substr($from, 0, $pos));
		elseif (($pos = strpos($from, '@')) > 0)
			return trim(substr($from, 0, $pos));
		else
			return $from;
	}

	static public function extractEmail(string $from): string
	{
		if (preg_match('/<(.+@.+)>/', $from, $match))
			return $match[1];
		elseif (preg_match('/([^\s]+@[^\s]+)/', $from, $match))
			return $match[1];
		elseif (preg_match('/\\((.+?)\\)/', $from, $match))
			return trim(str_replace($match[0], '', $from));
		else
			return $from;
	}

	public function name(): ?string
	{
		if ($user = $this->user()) {
			return $user->name();
		}
		elseif ($this->from_name) {
			return $this->from_name;
		}
		elseif (!$this->from_email) {
			return str_replace('@', ' at ', $this->from_email);
		}

		return null;
	}
}
