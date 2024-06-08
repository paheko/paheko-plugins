<?php

namespace Paheko\Plugin\Chat\Entities;

use Paheko\DB;
use Paheko\Entity;
use Paheko\ValidationException;
use Paheko\UserException;
use Paheko\Utils;
use Paheko\Users\Session;

use KD2\DB\EntityManager as EM;

class Channel extends Entity
{
	const TABLE = 'plugin_chat_channels';

	protected ?int $id;
	protected ?string $name = null;
	protected ?string $description = null;
	protected string $access;
	protected int $archived = 0;

	const ACCESS_PUBLIC = 'public'; // everyone
	const ACCESS_PRIVATE = 'private'; // logged-in users only
	const ACCESS_INVITE = 'invite'; // invited users/invited anonymous only
	const ACCESS_PM = 'pm'; // logged-in users only

	const ACCESS_LIST = [
		self::ACCESS_PUBLIC => 'Discussion publique',
		self::ACCESS_PRIVATE => 'Discussion privée, réservée aux membres connectés',
		self::ACCESS_INVITE => 'Discussion privée, sur invitation',
		self::ACCESS_PM => 'Discussion privée, entre deux personnes',
	];

	public function getAccessLabel(): string
	{
		return self::ACCESS_LIST[$this->access];
	}

	public function selfCheck(): void
	{
		parent::selfCheck();

		$this->assert(in_array($this->access, [self::ACCESS_PUBLIC, self::ACCESS_INVITE, self::ACCESS_PRIVATE, self::ACCESS_PM]));

		if ($this->access === self::ACCESS_PM) {
			$this->assert(!isset($this->name));
			$this->assert(!isset($this->description));
		}
		else {
			$this->assert(trim($this->name) !== '', 'Le nom ne peut rester vide.');
			$this->assert(strlen($this->name) <= 50, 'Le nom doit faire moins de 50 caractères.');
			$this->assert(preg_match('/^[^\x00\x07\x0A\x0D, :]+$/', $this->name), 'Le nom contient des caractères invalides.');
			$this->assert(!isset($this->description) || strlen($this->description) < 65000, 'La description doit faire moins de 65.000 caractères.');
		}
	}

	public function getUser(Session $session): ?User
	{
		$user_id = $session::getUserId();

		if ($user_id) {
			$user = EM::findOne(User::class, 'SELECT * FROM @TABLE WHERE id_channel = ? AND id_user = ?', $this->id(), $user_id);

			if (!$user) {
				$user = new User;
				$user->import([
					'id_channel' => $this->id(),
					'id_user'    => $user_id,
					'joined'     => time(),
					'name'       => $session->user()->name(),
				]);
				$user->save();
			}
			elseif ($user->name !== $session->user()->name()) {
				$user->set('name', $session->user()->name());
				$user->save();
			}

			return $user;
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

	public function getRecipient(User $me): ?User
	{
		if ($this->access !== self::ACCESS_PM) {
			return null;
		}

		return EM::findOne(User::class, 'SELECT * FROM @TABLE WHERE id_channel = ? AND id != ? LIMIT 1', $this->id(), $me->id());
	}

	public function say(User $user, string $text): Message
	{
		$now = time();

		$message = new Message;
		$message->import([
			'id_channel'   => $this->id(),
			'id_thread'    => null,
			'added'        => $now,
			'id_user'      => $user->id,
			'user_name'    => $user->id_user ? null : $user->name,
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
		$sql = 'SELECT * FROM @TABLE WHERE id_channel = ? AND (id_user IS NOT NULL OR last_disconnect < ?) ORDER BY name COLLATE U_NOCASE;';
		return EM::getInstance(User::class)->all($sql, $this->id(), time() - 3600);
	}

	public function listMessages(?int $before = null, ?int $count = 100): array
	{
		$clause = '';

		if ($before) {
			$clause = ' AND m.id < ' . (int)$before;
		}

		$sql = sprintf('SELECT m.*,
			CASE WHEN u.id IS NOT NULL THEN u.name ELSE m.user_name END AS user_name,
			CASE WHEN u.id_user IS NOT NULL THEN u.id_user ELSE NULL END AS real_user_id
			FROM plugin_chat_messages m
			LEFT JOIN plugin_chat_users u ON u.id = m.id_user
			WHERE m.id_channel = ? %s
			LIMIT 0, ?;',
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
