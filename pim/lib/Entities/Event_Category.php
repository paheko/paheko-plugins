<?php

namespace Paheko\Plugin\PIM\Entities;

use Paheko\Entity;

class Event_Category extends Entity
{
	const TABLE = 'plugin_pim_events_categories';

	protected ?int $id = null;
	protected int $id_user;
	protected string $title;
	protected int $default_reminder = 0;
	protected ?string $color;
	protected bool $is_default = false;

	public function save(bool $selfcheck = true): bool
	{
		if (!isset($this->color)) {
			$this->set('color', Calendar::getUniqueColor($this->title));
		}

		return parent::save($selfcheck);
	}

}
