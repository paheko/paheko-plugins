<?php

namespace Paheko\Plugin\PIM;

use Paheko\DB;

class ChangesTracker
{
	const ADDED = 1;
	const MODIFIED = 2;
	const DELETED = 3;

	const TABLE = 'plugin_pim_changes';

	public function record(string $entity, string $uri, int $type)
	{
		$db = DB::getInstance();

		if (!in_array($type, [self::ADDED, self::MODIFIED, self::DELETED])) {
			throw new InvalidArgumentException('Invalid change type');
		}

		// Only keep the last change
		$db->delete(self::TABLE, 'uri = ? AND entity = ?', $uri, $entity);

		return $db->insert(self::TABLE, compact('uri', 'type', 'entity'));
	}

	public function listChangesSince(string $entity, \DateTime $date)
	{
		$db = DB::getInstance();
		return $db->get(sprintf('SELECT uri, type FROM %s WHERE entity = ? AND timestamp >= ? ORDER BY timestamp DESC;', self::TABLE), $entity, $date);
	}
}
