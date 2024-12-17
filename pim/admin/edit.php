<?php

namespace Paheko\Plugin\PIM;

use Paheko\Config;
use Paheko\UserException;
use Paheko\Plugin\PIM\Entities\Event;
use KD2\I18N\TimeZones;

require __DIR__ . '/_inc.php';

$events = new Events($user_id);

if ($id = intval($_GET['id'] ?? 0)) {
	$event = $events->get($id);

	if (!$event) {
		throw new UserException('Catégorie inconnue');
	}

	if (!empty($_GET['copy'])) {
		$event = clone $event;
	}
}
else {
	$event = new Event;
	$event->id_user = $user_id;
	$event->id_category = $events->getDefaultCategory();

	$event->populateFromQueryString($_GET);
}

$csrf_key = 'pim_event_edit';


$form->runIf('import', function () use ($event) {
	if ($event->exists()) {
		throw new UserException('Invalid request', 400);
	}

	$event->populateFromVCalendarUpload('file');
	$event->save();

	Utils::redirect(sprintf('./?y=%d&m=%d', $event->date->format('Y'), $event->date->format('m')));
});

$form->runIf('save', function () use ($event) {
	$event->importForm();
	$event->save();

	Utils::redirect(sprintf('./?y=%d&m=%d', $event->date->format('Y'), $event->date->format('m')));
}, $csrf_key);

$title = $event->exists() ? 'Modifier un événement' : 'Nouvel événement';

$categories = $events->listCategories();
$categories_assoc = [];

foreach ($categories as $cat) {
	$categories_assoc[$cat->id] = $cat->title;
}

$config = Config::getInstance();
$default_tz = $events->getDefaultTimezone() ?: $config->timezone;
$start = new \DateTime('+1 hour');
$end = new \DateTime('+2 hour');

$timezones = TimeZones::listGroupedByContinent();

$tpl->assign(compact('event', 'csrf_key', 'title', 'categories', 'categories_assoc', 'timezones', 'default_tz', 'start', 'end'));

$tpl->display(__DIR__ . '/../templates/edit.tpl');
