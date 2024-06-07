<?php

namespace Paheko\Plugin\Chat\Entities;

use Paheko\DB;
use Paheko\Entity;
use Paheko\ValidationException;
use Paheko\UserException;
use Paheko\Utils;
use Paheko\Users\DynamicFields;
use Paheko\Users\Session;

use KD2\DB\EntityManager as EM;

class Channel extends Entity
{
	const TABLE = 'plugin_chat_channels';

	protected ?int $id;
	protected ?string $name;
	protected ?string $description;
	protected string $access;
	protected int $archived = 0;

	const ACCESS_PUBLIC = 'public'; // everyone
	const ACCESS_PRIVATE = 'private'; // logged-in users only
	const ACCESS_INVITE = 'invite'; // invited users/invited anonymous only
	const ACCESS_PM = 'pm'; // logged-in users only

	public function selfCheck(): void
	{
		parent::selfCheck();

		$this->assert(in_array($this->access, [self::ACCESS_PUBLIC, self::ACCESS_INVITE, self::ACCESS_PRIVATE, self::ACCESS_PM]));

		if ($this->access === self::ACCESS_PM) {
			$this->assert(null === $this->name);
			$this->assert(null === $this->description);
		}
		else {
			$this->assert(trim($this->name) !== '', 'Le nom ne peut rester vide.');
			$this->assert(strlen($this->name) < 200, 'Le nom doit faire moins de 200 caractères.');
			$this->assert(null === $this->description || strlen($this->description) < 65000, 'La description doit faire moins de 65.000 caractères.');
		}
	}

	public function getUser(Session $session): ?User
	{
		$user_id = $session::getUserId();

		if ($user_id) {
			return EM::findOne(User::class, 'SELECT * FROM @TABLE WHERE id_channel = ? AND id_user = ?', $this->id(), $user_id);
		}

		if (empty($_COOKIE['chat'])
			|| strlen($_COOKIE['chat']) !== 40
			|| ctype_alnum($_COOKIE['chat'])) {
			return null;
		}

		$session_id = $_COOKIE['chat'];
		return EM::findOne(User::class, 'SELECT * FROM @TABLE WHERE id_channel = ? AND invitation = ?', $this->id(), $session_id);
	}

	public function createAnonymousUser(string $name, ?string $invitation): User
	{
		if ($this->access === self::ACCESS_PM) {
			throw new ValidationException('You don\'t have access to this channel');
		}

		if (empty($invitation)
			|| strlen($invitation) !== 40
			|| ctype_alnum($invitation)) {
			$invitation = null;
		}

		$invitation ??= sha1(random_bytes(10));
		$user = EM::findOne(User::class, 'SELECT * FROM @TABLE WHERE id_channel = ? AND invitation = ?', $this->id(), $invitation);

		if (!$user && $this->access === self::ACCESS_INVITE) {
			throw new ValidationException('You don\'t have access to this channel');
		}
		elseif (!$user) {
			// Create user entity FIXME
		}

		setcookie('chat', $invitation, time() + 3600*24*365);
	}

	public function say(User $user, string $text): Message
	{
		$now = time();

		$message = new Message;
		$message->import([
			'id_channel'   => $this->id(),
			'id_thread'    => null,
			'added'        => $now,
			'id_user'      => $user->id_user,
			'user_name'    => $user->name,
			'type'         => Message::TYPE_TEXT,
			'content'      => trim($text),
			'reactions'    => null,
			'last_updated' => $now,
		]);

		$message->save();
		return $message;
	}

	public function requiresLogin(): bool
	{
		return $this->access !== self::ACCESS_PUBLIC;
	}

	public function listUsers(): array
	{
		$sql = sprintf('SELECT
			cu.id, cu.id_user, cu.joined, cu.last_disconnect, cu.last_seen_message_id,
			CASE WHEN u.id IS NOT NULL THEN %s ELSE cu.name END AS name
			FROM plugin_chat_users cu
			LEFT JOIN users u ON u.id = cu.id_user
			WHERE cu.id_channel = ? AND (cu.id_user IS NOT NULL OR cu.last_disconnect < ?)
			ORDER BY name COLLATE NOCASE;',
			DynamicFields::getNameFieldsSQL()
		);

		return DB::getInstance()->get($sql, $this->id(), time() - 3600);
	}

	public function listMessages(?int $before = null, ?int $count = 100): array
	{
		$clause = '';

		if ($before) {
			$clause = ' AND m.id < ' . (int)$before;
		}

		$sql = sprintf('SELECT m.*,
			CASE WHEN u.id IS NOT NULL THEN %s ELSE m.user_name END AS user_name
			FROM plugin_chat_messages m
			LEFT JOIN users u ON u.id = m.id_user
			WHERE m.id_channel = ? %s
			LIMIT 0, ?;',
			DynamicFields::getNameFieldsSQL(),
			$clause
		);

		return DB::getInstance()->get($sql, $this->id(), $count);
	}

	public function getEventsSince(int $since, User $user): \Generator
	{
		$db = DB::getInstance();
		// Delete old anonymous users
		$db->delete('plugin_chat_users', 'id_user IS NULL AND last_disconnect < ' . (time() - 3600*24));

		foreach ($db->iterate('SELECT * FROM plugin_chat_users WHERE last_disconnect > ?;', $since + 15) as $user) {
			yield ['type' => 'user_offline', 'data' => $user];
		}

		foreach ($db->iterate('SELECT * FROM plugin_chat_users WHERE joined > ?;', $since) as $user) {
			yield ['type' => 'user_joined', 'data' => $user];
		}

		foreach ($db->iterate('SELECT * FROM plugin_chat_messages WHERE last_updated > ? ORDER BY added, id;', $since) as $message) {
			if ($message->last_updated !== $message->added) {
				yield ['type' => 'message_updated', 'data' => $message];
			}
			else {
				yield ['type' => 'message_new', 'data' => $message];
			}
		}
	}
}
