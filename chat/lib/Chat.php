<?php

namespace Paheko\Plugin\Chat;

use Paheko\Plugin\Chat\Entities\Channel;
use Paheko\Plugin\Chat\Entities\User;
use Paheko\Plugin\Chat\Entities\Message;
use Paheko\Users\Session;
use Paheko\DB;

use KD2\DB\EntityManager as EM;

class Chat
{
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

		return $user;
	}

	static public function hasPublicChannels(): bool
	{
		return DB::getInstance()->test(Channel::TABLE, 'access = ?', Channel::ACCESS_PUBLIC);
	}

	static public function getChannel(int $id, User $user): ?Channel
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
			$me->id_user,
			$recipient->id_user,
			Channel::ACCESS_DIRECT
		);

		if (!$channel) {
			$channel = new Channel;
			$channel->import([
				'access' => $channel::ACCESS_DIRECT,
			]);
			$channel->save();

			$channel->addUser($me);
			$channel->addUser($recipient);
		}

		return $channel;
	}

	static public function getFallbackChannel(User $user): ?Channel
	{
		$channel = EM::findOne(Channel::class, 'SELECT * FROM @TABLE
			WHERE id = (SELECT id_channel FROM plugin_chat_users_channels WHERE id_user = ? ORDER BY last_connect DESC LIMIT 1);', $user->id);

		if (!$channel && $user->id_user) {
			$channel = EM::findOne(Channel::class, 'SELECT * FROM @TABLE WHERE access = ? LIMIT 1;', Channel::ACCESS_PRIVATE);
		}

		return $channel;
	}

	static public function listChannels(Session $session): array
	{
		$params = [Channel::ACCESS_PUBLIC, Channel::ACCESS_PUBLIC];
		$access = '';

		if ($session->isLogged()) {
			$access = ' OR access = ? OR id IN (SELECT id_channel FROM plugin_chat_users_channels WHERE id_user = ?)';
			$params[] = Channel::ACCESS_PRIVATE;
			$params[] = $session::getUserId();

			$pm_name = 'id_user != ?';
			$params[] = $session::getUserId();
		}
		else {
			$pm_name = 'invitation != ?';
			$params[] = self::getAnonymousSessionId();
		}

		$sql = sprintf('SELECT *, CASE WHEN access = ? THEN (SELECT name FROM plugin_chat_users WHERE %s LIMIT 1) ELSE name END AS name
			FROM @TABLE WHERE access = ? %s ORDER BY access = \'direct\', name COLLATE NOCASE;', $pm_name, $access);
		return EM::getInstance(Channel::class)->all($sql, ...$params);
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
