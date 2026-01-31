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

	public function isAnonymous(): bool
	{
		return $this->id_user === null;
	}

	public function disconnect(): void
	{
		$this->set('last_disconnect', time());
		$this->save();
	}

	public function getStatus(): string
	{
		if (!$this->last_connect) {
			return 'offline';
		}

		$now = time();

		// Online is when last connect is more recent than 10 minutes
		// as connect.php requires a reconnect every 10 minutes
		if ($this->last_connect > $this->last_disconnect
			&& $this->last_connect >= $now - 600) {
			return 'online';
		}
		// You can be inactive for 2 minutes
		elseif ($this->last_disconnect <= $now - 120) {
			return 'inactive';
		}
		else {
			return 'offline';
		}
	}

	public function isOnline(): bool
	{
		return $this->getStatus() === 'online';
	}
}
