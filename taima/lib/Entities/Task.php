<?php

namespace Garradin\Plugin\Taima\Entities;

use Garradin\Entity;

class Task extends Entity
{
	const TABLE = 'plugin_taima_tasks';

	protected $id;
	protected $label;

	protected $_types = [
		'id'    => 'int',
		'label' => 'string',
	];
}
