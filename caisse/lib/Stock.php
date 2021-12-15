<?php

namespace Garradin\Plugin\Caisse;

use Garradin\DB;
use KD2\DB\EntityManager as EM;

use Garradin\Plugin\Caisse\Entities\StockEvent;

class Stock
{
	static public function get(int $id): ?StockEvent
	{
		return EM::findOneById(StockEvent::class, $id);
	}

	static public function new(): StockEvent
	{
		return new StockEvent;
	}

	static public function listEvents(): array
	{
		return EM::getInstance(StockEvent::class)->all('SELECT * FROM @TABLE ORDER BY date DESC;');
	}
}
