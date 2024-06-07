<?php

namespace Paheko\Plugin\Chat;

use Paheko\Plugin\Chat\Entities\Channel;
use Paheko\Plugin\Chat\Entities\User;
use Paheko\Plugin\Chat\Entities\Message;
use Paheko\Users\Session;

use KD2\DB\EntityManager as EM;

class Chat
{
	static public function getChannel(int $id): ?Channel
	{
		return EM::findOneById(Channel::class, $id);
	}

	static public function listChannels(Session $session): array
	{
		$params = [Channel::ACCESS_PUBLIC];
		$access = '';

		if ($session->isLogged()) {
			$access = ' OR access = ? OR id IN (SELECT id_channel FROM plugin_chat_users WHERE id_user = ?)';
			$params[] = Channel::ACCESS_PRIVATE;
			$params[] = $session::getUserId();
		}

		$sql = sprintf('SELECT * FROM @TABLE WHERE access = ? %s ORDER BY name COLLATE NOCASE;', $access);
		return EM::getInstance(Channel::class)->all($sql, ...$params);
	}
}
