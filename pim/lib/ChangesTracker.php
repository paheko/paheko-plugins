<?php

namespace Paheko\Plugin\PIM;

use Paheko\DB;

class ChangesTracker
{
	const ADDED = 1;
	const MODIFIED = 2;
	const DELETED = 3;

	const TABLE = 'plugin_pim_changes';

	static public function record(int $id_user, string $entity, string $uri, int $type)
	{
		$db = DB::getInstance();

		if (!in_array($type, [self::ADDED, self::MODIFIED, self::DELETED])) {
			throw new InvalidArgumentException('Invalid change type');
		}

		// Only keep the last change
		$db->delete(self::TABLE, 'id_user = ? AND uri = ? AND entity = ?', $id_user, $uri, $entity);

		return $db->insert(self::TABLE, compact('id_user', 'uri', 'type', 'entity'));
	}

	static public function listChangesSince(int $id_user, string $entity, \DateTime $date)
	{
		$db = DB::getInstance();
		return $db->get(sprintf('SELECT uri, type FROM %s
			WHERE id_user = ? AND entity = ? AND timestamp >= ?
			ORDER BY timestamp DESC;', self::TABLE),
			$id_user,
			$entity,
			$date
		);
	}
}
