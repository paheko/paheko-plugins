<?php

namespace Paheko\Plugin\PIM;

use Paheko\Config;
use Paheko\UserException;
use Paheko\Utils;
use Paheko\Plugin\PIM\Entities\Event;
use KD2\I18N\TimeZones;
use Paheko\Users\Session;

require __DIR__ . '/_inc.php';

$events = new Events(Session::getUserId());

$id = intval($_GET['copy'] ?? ($_GET['id'] ?? 0));

if ($id) {
	$event = $events->get($id);

	if (!$event) {
		throw new UserException('Événment introuvable');
	}

	if (!empty($_GET['copy'])) {
		$event = clone $event;
	}
}
else {
	$event = new Event;
	$event->id_user = $user_id;
	$event->id_category = $events->getDefaultCategory();

	$event->populateFromQueryString($events, $_GET);
}

$csrf_key = 'pim_event_edit';

$form->runIf('import', function () use ($event) {
	if ($event->exists()) {
		throw new UserException('Invalid request', 400);
	}

	$event->populateFromVCalendarUpload('file');
	$event->save();

	Utils::reloadParentFrame(sprintf('./?y=%d&m=%d', $event->start->format('Y'), $event->start->format('m')));
});

$form->runIf('save', function () use ($event) {
	$event->importForm();
	$event->save();

	Utils::reloadParentFrame(sprintf('./?y=%d&m=%d', $event->start->format('Y'), $event->start->format('m')));
}, $csrf_key);

$title = $event->exists() ? 'Modifier un événement' : 'Nouvel événement';

$categories = $events->listCategories();
$categories_export = [];
$categories_assoc = [];

foreach ($categories as $cat) {
	$categories_assoc[$cat->id] = $cat->title;
	$categories_export[$cat->id] = $cat->asArray();
}

$event->timezone ??= $events->getDefaultTimezone();
$event->start ??= new \DateTime('+1 hour');
$event->end ??= new \DateTime('+2 hour');

$default_cat = $events->getDefaultCategory();
$event->id_category = $default_cat;
$event->reminder = $categories_export[$default_cat]->default_reminder ?? 0;

$timezones = TimeZones::listGroupedByContinent();

$tpl->assign(compact('event', 'csrf_key', 'title', 'categories_assoc', 'categories_export', 'timezones'));

$tpl->display(__DIR__ . '/../templates/edit.tpl');
