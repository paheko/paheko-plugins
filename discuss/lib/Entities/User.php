<?php

namespace Paheko\Plugin\Discuss\Entities;

use Paheko\Entity;

use DateTime;

class User extends Entity
{
	const TABLE = 'plugin_discuss_users';

	/**
	 * Don't send any e-mail (forum mode)
	 */
	const SUBSCRIPTION_NONE = 0;

	/**
	 * Stop sending e-mails, after address has bounced
	 */
	const SUBSCRIPTION_DISABLED = -1;

	/**
	 * Send each message
	 */
	const SUBSCRIPTION_ALL = 1;

	// TODO:
	//const SUBSCRIPTION_DIGEST = 2;

	const MODERATOR = 0x01 << 1;
	const BANNED = 0x01 << 2;

	protected ?int $id;
	protected string $email;
	protected ?string $name = null;
	protected ?string $password = null;
	protected int $status = 0;
	protected int $subscription = 0;
	protected int $stats_posts = 0;
	protected int $stats_bounced = 0;
	protected DateTime $created;
	protected ?DateTime $last_post;

	public function isModerator(): bool
	{
		return $this->status & self::MODERATOR;
	}
}
