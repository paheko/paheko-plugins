<?php

namespace Paheko\Plugin\Chat;

use Paheko\Plugin\Chat\Entities\Channel;
use Paheko\Plugin\Chat\Entities\User;
use Paheko\Plugin\Chat\Entities\Message;
use Paheko\Users\Session;

use KD2\DB\EntityManager as EM;

class Chat
{
	static public function getChannel(int $id, ?Session $session = null): ?Channel
	{
		$channel = EM::findOneById(Channel::class, $id);

		if (!$channel || !$session) {
			return $channel;
		}

		if ($channel->requiresLogin() && !$session->isLogged()) {
			throw new UserException('You cannot access this channel', 403);
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
	 * Get a user channel between two logged-in users
	 */
	static public function getUserChannel(Session $session, int $recipient_user_id)
	{
		if (!$session->isLogged()) {
			throw new \LogicException('Only logged-in users can use this method');
		}


	}

	/**
	 * Create or return an existing channel between two users of an existing channel
	 */
	static public function getPMChannel(User $me, int $recipient_id): ?Channel
	{
		$recipient = EM::findOneById(User::class, $recipient_id);

		if (!$recipient || $recipient->id_channel !== $me->id_channel) {
			throw new \LogicException('You need to be in a channel with someone to be able to message them');
		}

		$channel = EM::findOne(Channel::class, 'SELECT c.* FROM @TABLE c
			INNER JOIN plugin_chat_users u1 ON u1.id_channel = c.id AND u1.id != u2.id AND ((u1.id_user IS NOT NULL AND u1.id_user = ?) OR (u1.invitation IS NOT NULL AND u1.invitation = ?))
			INNER JOIN plugin_chat_users u2 ON u2.id_channel = c.id AND u1.id != u2.id AND ((u2.id_user IS NOT NULL AND u2.id_user = ?) OR (u2.invitation IS NOT NULL AND u2.invitation = ?))
			WHERE c.access = ? GROUP BY c.id LIMIT 1;',
			$me->id_user,
			$me->invitation,
			$recipient->id_user,
			$recipient->invitation,
			Channel::ACCESS_PM
		);

		if (!$channel) {
			$channel = new Channel;
			$channel->import([
				'access' => $channel::ACCESS_PM,
			]);
			$channel->save();

			$user = new User;
			$user->import([
				'id_channel' => $channel->id(),
				'id_user'    => $me->id_user,
				'invitation' => $me->invitation,
				'joined'     => time(),
				'name'       => $me->name,
			]);
			$user->save();

			$user = new User;
			$user->import([
				'id_channel' => $channel->id(),
				'id_user'    => $recipient->id_user,
				'invitation' => $recipient->invitation,
				'joined'     => time(),
				'name'       => $recipient->name,
			]);
			$user->save();
		}

		return $channel;
	}

	static public function getFallbackChannel(?Session $session = null): ?Channel
	{
		$user_id = $session::getUserId();

		if ($user_id) {
			$channel = EM::findOne(Channel::class, 'SELECT * FROM @TABLE
				WHERE id = (SELECT id_channel FROM plugin_chat_users WHERE id_user = ? ORDER BY last_disconnect DESC LIMIT 1);', $user_id);

			if (!$channel) {
				$channel = EM::findOne(Channel::class, 'SELECT * FROM @TABLE WHERE access = \'private\' LIMIT 1;');
			}

			return $channel;
		}

		if (empty($_COOKIE['chat'])
			|| strlen($_COOKIE['chat']) !== 40
			|| ctype_alnum($_COOKIE['chat'])) {
			return null;
		}

		$session_id = $_COOKIE['chat'];
		return EM::findOne(User::class, 'SELECT * FROM @TABLE WHERE id = (SELECT id_channel FROM plugin_chat_users WHERE invitation = ? ORDER BY last_disconnect DESC LIMIT 1;', $this->id(), $session_id);
	}

	static public function listChannels(Session $session): array
	{
		$params = [Channel::ACCESS_PUBLIC];
		$access = '';

		if ($session->isLogged()) {
			$access = ' OR access = ? OR id IN (SELECT id_channel FROM plugin_chat_users WHERE id_user = ?)';
			$params[] = Channel::ACCESS_PRIVATE;
			$params[] = $session::getUserId();

			$pm_name = 'id_user != ?';
			$params[] = $session::getUserId();
		}
		else {
			$pm_name = 'invitation != ?';
			$params[] = self::getAnonymousSessionId();
		}

		$sql = sprintf('SELECT *, CASE WHEN access = \'pm\' THEN (SELECT name FROM plugin_chat_users WHERE %s LIMIT 1) ELSE name END AS name
			FROM @TABLE WHERE access = ? %s ORDER BY access = \'pm\', name COLLATE NOCASE;', $pm_name, $access);
		return EM::getInstance(Channel::class)->all($sql, ...$params);
	}
}
