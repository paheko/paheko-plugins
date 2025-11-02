<?php

namespace Paheko\Plugin\Chat;

use Paheko\Plugin\Chat\Entities\Channel;
use Paheko\Plugin\Chat\Entities\User;
use Paheko\Plugin\Chat\Entities\Message;
use Paheko\Users\Session;
use Paheko\Users\Users as PahekoMembers;
use Paheko\DB;
use Paheko\UserException;

use KD2\DB\EntityManager as EM;

class Chat
{
	static public function createAnonymousUser(string $name): User
	{
		if (!self::hasPublicChannels()) {
			throw new \LogicException('No public channels');
		}

		$name = trim($name);

		if (!$name || !preg_match('!^[a-z][a-z0-9_]{1,15}$!i', $name)) {
			throw new UserException('Pseudonyme invalide');
		}

		$db = DB::getInstance();

		if ($db->test(User::TABLE, 'name = ?', $name)) {
			throw new UserException('Ce pseudo est déjà pris, merci d\'en choisir un autre.');
		}

		$user = new User;
		$user->set('session_id', sha1(random_bytes(16)));
		$user->set('name', $name);
		$user->set('last_connect', time());
		$user->save();

		setcookie('chat_session', $user->session_id, time() + 3600 * 24 * 7, '/');
		return $user;
	}

	static public function pruneAnonymousUsers()
	{
		$expire = time() - 3600 * 24 * 7;
		$db = DB::getInstance();
		$db->preparedQuery('DELETE FROM plugin_chat_users WHERE session_id IS NOT NULL AND (
			(last_disconnect IS NULL AND last_connect < ?)
			OR (last_disconnect IS NOT NULL AND last_disconnect < ?));', $expire, $expire);
	}

	static public function getUser(): ?User
	{
		$session = Session::getInstance();
		$user_id = $session::getUserId();
		$user = null;

		if ($user_id) {
			$user = EM::findOne(User::class, 'SELECT * FROM @TABLE WHERE id_user = ?', $user_id);

			if (!$user) {
				$user = new User;
				$user->import([
					'id_user'      => $user_id,
					'name'         => $session->user()->name(),
					'last_connect' => time(),
				]);
				$user->save();
			}
			elseif ($user->name !== $session->user()->name()) {
				$user->set('name', $session->user()->name());
				$user->save();
			}
		}
		elseif (!empty($_COOKIE['chat_session'])) {
			$user = EM::findOne(User::class, 'SELECT * FROM @TABLE WHERE session_id = ?', $_COOKIE['chat_session']);
		}

		return $user;
	}

	static public function hasPublicChannels(): bool
	{
		return DB::getInstance()->test(Channel::TABLE, 'access = ?', Channel::ACCESS_PUBLIC);
	}

	static public function getMessage(int $id, ?User $user): Message
	{
		if ($user) {
			$message = EM::findOne(Message::class, 'SELECT * FROM @TABLE WHERE id = ? AND id_user = ?;', $id, $user->id());
		}
		else {
			$message = EM::findOne(Message::class, 'SELECT * FROM @TABLE WHERE id = ?;', $id);
		}

		if (!$message) {
			throw new UserException('This message does not exist or does not belong to you', 404);
		}

		return $message;
	}

	static public function getChannel(int $id, User $user): Channel
	{
		$channel = EM::findOneById(Channel::class, $id);

		if (!$channel) {
			throw new UserException('This channel does not exist', 404);
		}

		$session = Session::getInstance();

		if ($channel->requiresLogin() && !$session->isLogged()) {
			throw new UserException('You cannot access this channel', 403);
		}
		elseif ($channel->access === Channel::ACCESS_INVITE) {
			throw new UserException('FIXME', 403);
		}
		elseif ($channel->access === Channel::ACCESS_DIRECT) {
			if (!DB::getInstance()->test('plugin_chat_users_channels', 'id_user = ?', $user->id)) {
				throw new UserException('You cannot access this channel', 403);
			}
		}

		return $channel;
	}

	static public function getAnonymousSessionId(): ?string
	{
		$id = $_COOKIE['chat'] ?? null;

		if (empty($id)) {
			return null;
		}

		if (strlen($id) !== 40) {
			return null;
		}

		if (!ctype_alnum($id)) {
			return null;
		}

		return $id;
	}

	/**
	 * Create a channel with a member user, not a chat user
	 */
	static public function getDirectChannelUser(User $me, int $user_id): ?Channel
	{
		$db = DB::getInstance();
		$recipient_id = $db->firstColumn('SELECT id FROM plugin_chat_users WHERE id_user = ?;', $user_id);

		// Create chat user first
		if (!$recipient_id) {
			$member = PahekoMembers::get($user_id);

			if (!$member) {
				throw new UserException('Ce membre n\'existe pas;');
			}

			$user = new User;
			$user->import([
				'id_user'      => $member->id(),
				'name'         => $member->name(),
			]);
			$user->save();
			$recipient_id = $user->id();
		}

		return self::getDirectChannel($me, $recipient_id);
	}

	/**
	 * Create or return an existing channel between two users of an existing channel
	 */
	static public function getDirectChannel(User $me, int $recipient_id): ?Channel
	{
		$recipient = EM::findOneById(User::class, $recipient_id);

		if (!$recipient) {
			return null;
		}

		// Anonymous users cannot talk to just everyone, they need to be in the same channel
		if (!$me->id_user) {
			$same_channel = DB::getInstance()->firstColumn('SELECT 1
				FROM plugin_chat_users_channels a
				INNER JOIN plugin_chat_users_channels b
				WHERE b.id_channel = a.id_channel
					AND a.id_user = ?
					AND b.id_user = ?;',
					$me->id,
					$recipient->id);

			if (!$same_channel) {
				throw new \LogicException('You need to be in a channel with someone to be able to message them');
			}
		}

		$channel = EM::findOne(Channel::class, 'SELECT c.*
			FROM plugin_chat_channels c
			INNER JOIN plugin_chat_users_channels a ON a.id_channel = c.id AND a.id_user = ?
			INNER JOIN plugin_chat_users_channels b ON b.id_channel = c.id AND b.id_user = ?
			WHERE c.access = ? GROUP BY c.id LIMIT 1;',
			$me->id,
			$recipient->id,
			Channel::ACCESS_DIRECT
		);

		if (!$channel) {
			$channel = new Channel;
			$channel->import([
				'access' => $channel::ACCESS_DIRECT,
			]);
			$channel->save();

			$channel->addUser($me);

			// Don't add myself twice if the channel is with myself
			if ($recipient->id !== $me->id) {
				$channel->addUser($recipient);
			}
		}

		return $channel;
	}

	static public function createChannel(): Channel
	{
		$channel = new Channel;
		$channel->import([
			'access' => $channel::ACCESS_PRIVATE,
		]);
		return $channel;
	}

	static public function getFallbackChannel(User $user): ?Channel
	{
		$channel = EM::findOne(Channel::class, 'SELECT * FROM @TABLE
			WHERE id = (SELECT id_channel FROM plugin_chat_users_channels WHERE id_user = ? ORDER BY last_connect DESC LIMIT 1);', $user->id);

		if (!$channel && $user->id_user) {
			$channel = EM::findOne(Channel::class, 'SELECT * FROM @TABLE WHERE access = ? LIMIT 1;', Channel::ACCESS_PRIVATE);
		}
		elseif (!$channel) {
			$channel = EM::findOne(Channel::class, 'SELECT * FROM @TABLE WHERE access = ? LIMIT 1;', Channel::ACCESS_PUBLIC);
		}

		return $channel;
	}

	static public function listChannels(User $user): array
	{
		$private_access = '';

		if ($user->id_user) {
			$private_access = sprintf('OR access = \'%s\'', Channel::ACCESS_PRIVATE);
		}

		$sql = sprintf('SELECT c.id, c.access, COALESCE(u2.name, c.name) AS name
			FROM plugin_chat_channels c
			LEFT JOIN plugin_chat_users_channels uc1 ON c.access = \'direct\' AND uc1.id_channel = c.id AND uc1.id_user = %d
			LEFT JOIN plugin_chat_users_channels uc2 ON c.access = \'direct\' AND uc2.id_channel = c.id AND uc2.rowid != uc1.rowid
			LEFT JOIN plugin_chat_users u2 ON u2.id = uc2.id_user
			WHERE c.access = \'direct\' OR (access = \'public\' %s)
			GROUP BY c.id
			ORDER BY access = \'direct\', name COLLATE NOCASE;', $user->id, $private_access);

		return DB::getInstance()->get($sql);
	}

	static public function getUsersNames(array $ids): array
	{
		static $users = [];

		$out = [];

		// Use cache
		foreach ($ids as $key => $id) {
			if (array_key_exists($id, $users)) {
				$out[] = $users[$id];
				unset($ids[$key]);
			}
		}

		if (!count($ids)) {
			return $out;
		}

		$db = DB::getInstance();
		$sql = sprintf('SELECT id, name FROM plugin_chat_users WHERE %s AND name IS NOT NULL;', $db->where('id', $ids));

		foreach ($db->iterate($sql) as $row) {
			$out[] = $row->name;
			$users[$row->id] = $row->name;
		}

		return $out;
	}
}
