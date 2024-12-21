<?php


class Users
{
	static public function get(int $id): ?User
	{
		return EntityManager::findOneById(User::class, $id);
	}

	static public function iterate(): array
	{
		return EntityManager::getInstance(User::class)->iterate('SELECT * FROM @TABLE ORDER BY status, email;');
	}

	static public function count(): array
	{
		return EntityManager::getInstance(User::class)->col('SELECT COUNT(*) FROM @TABLE;');
	}

	static public function subscribeFromString(string $list, bool $send_welcome_message): int
	{
		$list = explode("\n", $list);
		$list = array_map('trim', $list);
		return self::subscribeFromArray($list);
	}

	static public function subscribeFromArray(array $list, bool $send_welcome_message): int
	{
		$list = array_filter($list);

		foreach ($list as $line) {
			if (!filter_var($line, FILTER_VALIDATE_EMAIL)) {
				throw new NG_User_Exception(sprintf('"%s" is not a valid email address.', $line));
			}
		}

		foreach ($list as $line) {
			self::subscribe($line, $send_welcome_message);
		}

		return count($list);
	}

	static public function subscribe(string $email, bool $send_welcome_message): void
	{
		$user = new User;
		$user->email = $email;
		$user->subscription = $user::SUBSCRIPTION_ALL;
		$user->created = new \DateTime;
		$user->save();

		if ($send_welcome_message) {
			self::sendWelcome($list, $line);
		}
	}

	static public function setModeratorFlag(array $list, bool $moderator): void
	{
		$list = array_map('intval', $list);

		$update = sprintf('status = status & %s%d', $moderator ? '' : '~', User::MODERATOR);
		$where = sprintf('id IN (%s)', implode(',', $list));

		$db->update(User::TABLE, $update, $where);
	}

}
