<?php

namespace Paheko\Plugin\PIM;

use Paheko\Config;
use Paheko\UserException;
use Paheko\Utils;
use Paheko\Plugin\PIM\Entities\Event;
use KD2\I18N\TimeZones;

require __DIR__ . '/_inc.php';

$events = new Events($user_id);

$id = intval($_GET['id'] ?? 0);

$event = $events->get($id);

if (!$event) {
	throw new UserException('Événment introuvable');
}

$csrf_key = 'pim_event_delete';

$form->runIf('delete', function () use ($event) {
	$url = sprintf('./?y=%d&m=%d', $event->start->format('Y'), $event->start->format('m'));
	$event->delete();

	Utils::reloadParentFrame($url);
}, $csrf_key);

$tpl->assign(compact('event', 'csrf_key'));

$tpl->display(__DIR__ . '/../templates/delete.tpl');
