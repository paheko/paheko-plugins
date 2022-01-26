<?php

namespace Garradin\Plugin\Caisse;

use Garradin\DB;
use KD2\DB\EntityManager as EM;

use Garradin\Plugin\Caisse\Entities\Method;

class Methods
{
	static public function get(int $id): ?Method
	{
		return EM::findOneById(Method::class, $id);
	}

	static public function new(): Method
	{
		return new Method;
	}

	static public function list(): array
	{
		return EM::getInstance(Method::class)->all('SELECT * FROM @TABLE ORDER BY name;');
	}
}
