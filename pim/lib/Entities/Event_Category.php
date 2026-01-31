<?php

namespace Paheko\Plugin\PIM\Entities;

use Paheko\DB;
use Paheko\Entity;

class Event_Category extends Entity
{
	const TABLE = 'plugin_pim_events_categories';

	protected ?int $id = null;
	protected int $id_user;
	protected string $title;
	protected int $default_reminder = 0;
	protected ?int $color;
	protected bool $is_default = false;

	public function save(bool $selfcheck = true): bool
	{
		if (!isset($this->color)) {
			$this->set('color', Calendar::getUniqueColor($this->title));
		}

		return parent::save($selfcheck);
	}

	public function delete(): bool
	{
		$db = DB::getInstance();
		$db->begin();

		$default = $db->firstColumn('SELECT id FROM plugin_pim_events_categories WHERE id_user = ? ORDER BY is_default DESC LIMIT 1;', $this->id_user);
		$db->exec(sprintf('UPDATE plugin_pim_events SET id_category = %d WHERE id_category = %d AND id_user = %d;',
			$default,
			$this->id(),
			$this->id_user
		));

		$ok = parent::delete();
		$db->commit();
		return $ok;
	}
}
