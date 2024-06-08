<?php

namespace Paheko\Plugin\Chat\Entities;

use Paheko\DB;
use Paheko\Entity;
use Paheko\ValidationException;
use Paheko\UserException;
use Paheko\Utils;

class User extends Entity
{
	const TABLE = 'plugin_chat_users';

	protected ?int $id;
	protected int $id_channel;
	protected ?int $id_user;
	protected ?string $name;
	protected ?string $invitation;
	protected ?int $joined;
	protected ?int $last_disconnect;
	protected ?int $last_seen_message_id;

	public function disconnect()
	{
		$this->set('last_disconnect', time());
		$this->save();
	}

	public function isOnline()
	{
		return $this->joined && (!$this->last_disconnect || $this->last_disconnect < time() - 1);
	}
}
