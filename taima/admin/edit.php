<?php

namespace Paheko\Plugin\Taima;

use Paheko\Plugin\Taima\Tracking;
use Paheko\Plugin\Taima\Entities\Entry;
use Paheko\Form;
use Paheko\Users\Users;
use Paheko\Utils;
use Paheko\UserException;

use function Paheko\{f, qg};

use KD2\DB\Date;

require_once __DIR__ . '/_inc.php';

$csrf_key = 'edit_task';
$selected_user = null;

$user = qg('id_user');

if ($user) {
	$user = Users::get((int)$user);

	if (!$user) {
		throw new UserException('Membre inconnu');
	}

	$selected_user = [$user->id => $user->name()];
}

if (qg('from')) {
	$entry = Tracking::get((int)qg('from'));
	$entry = clone $entry;
	$entry_duration = Tracking::formatMinutes($entry->duration);
}
elseif (qg('id')) {
	$entry = Tracking::get((int)qg('id'));
	$entry_duration = Tracking::formatMinutes($entry->duration);
	$selected_user = $user ? [$user->id => $user->name()] : null;
}
else {
	$entry = new Entry;
	$entry_duration = null;

	if (isset($_GET['date'])) {
		$entry->importForm(['date' => $_GET['date'], 'user_id' => $session::getUserId()]);
	}
}

if (!$session->canAccess($session::SECTION_USERS, $session::ACCESS_WRITE)
	&& (!$session->isLogged() || $entry->user_id !== $session::getUserId())) {
	throw new UserException('Vous n\'avez pas accès à cette tâche');
}

$form->runIf('save', function () use ($entry) {
	$entry->importForm();
	$entry->save();
}, $csrf_key, Utils::getSelfURI(['ok' => 1]));

$tasks = Tracking::listTasks();
$now = new Date;
$date = isset($_GET['date']);
$is_today = $date && $_GET['date'] === date('Y-m-d');
$submit_label = $is_today && !$entry->duration ? 'Démarrer le chrono' : 'Enregistrer';

$tpl->assign(compact('tasks', 'csrf_key', 'now', 'selected_user', 'entry', 'entry_duration', 'date', 'is_today', 'submit_label'));

$tpl->display(\Paheko\PLUGIN_ROOT . '/templates/edit.tpl');
