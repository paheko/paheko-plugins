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
	protected ?int $id_user = null;
	protected ?string $name;
	protected ?string $session_id = null;
	protected ?int $last_connect = null;
	protected ?int $last_disconnect = null;

	public function disconnect(): void
	{
		$this->set('last_disconnect', time());
		$this->save();
	}

	public function isOnline(): bool
	{
		if (!$this->last_connect) {
			return false;
		}

		$now = time();

		if ($this->last_connect > $this->last_disconnect && $this->last_connect >= $now - 60) {
			return true;
		}

		if (!$this->last_disconnect) {
			return true;
		}

		if ($this->last_disconnect >= $now - 15) {
			return true;
		}

		return false;
	}
}
