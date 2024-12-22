<?php

namespace Paheko\Plugin\Discuss\Entities;

use Paheko\Entity;

use DateTime;

class User extends Entity
{
	const TABLE = 'plugin_discuss_users';

	protected ?int $id;
	protected ?int $id_user;
	protected int $id_forum;
	protected ?string $email;
	protected ?string $name = null;
	protected ?string $password = null;
	protected bool $is_moderator = false;
	protected bool $is_banned = false;
	protected bool $subscribed = false;
	protected int $stats_posts = 0;
	protected int $stats_bounced = 0;
	protected DateTime $created;
	protected ?DateTime $last_post;
	protected bool $has_avatar = false;
	protected ?string $pgp_key = null;

	public function isModerator(): bool
	{
		return $this->status & self::MODERATOR;
	}

	public function email(): ?string
	{
		if ($this->id_user) {
			return $this->user()->email();
		}

		return $this->email;
	}
}
