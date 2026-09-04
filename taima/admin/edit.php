<?php

namespace Paheko\Plugin\Taima;

use Paheko\Plugin\Taima\Tracking;
use Paheko\Plugin\Taima\Entities\Entry;
use Paheko\Form;
use Paheko\Users\Session;
use Paheko\Users\Users;
use Paheko\Utils;
use Paheko\UserException;

use KD2\DB\Date;

require_once __DIR__ . '/_inc.php';

$csrf_key = 'edit_task';
$selected_user = null;
$session = Session::getInstance();

// Pre-fill user name
if ($session->canAccess($session::SECTION_USERS, $session::ACCESS_WRITE)
	&& isset($_GET['id_user'])) {
	$user = Users::get((int) $_GET['id_user']);

	if (!$user) {
		throw new UserException('Membre inconnu');
	}

	$selected_user = [$user->id => $user->name()];
}

// Duplicate from ID
if (isset($_GET['from'])) {
	$entry = Tracking::get((int) $_GET['from']);
	$entry = clone $entry;
	$entry_duration = Tracking::formatMinutes($entry->duration);
}
// Edit existing entry
elseif (isset($_GET['id'])) {
	$entry = Tracking::get((int) $_GET['id']);
	$entry_duration = Tracking::formatMinutes($entry->duration);
}
// Create new entry
else {
	$entry = new Entry;
	$entry_duration = null;
	$entry->set('user_id', $session::getUserId());

	// If date is set, it means it's for the current user
	if (isset($_GET['date'])) {
		$entry->importForm(['date' => $_GET['date']]);
	}
}

if (!$session->canAccess($session::SECTION_USERS, $session::ACCESS_WRITE)
	&& (!$session->isLogged() || $entry->user_id !== $session::getUserId())) {
	throw new UserException('Vous n\'avez pas accès à cette tâche');
}

if ($entry->exists() || isset($_GET['from'])) {
	$selected_user ??= $entry->user_id ? [$entry->user_id => $entry->user_name()] : null;
}

$form->runIf('save', function () use ($entry, $session) {
	$data = $_POST;

	// Users who can only create entries cannot create entries other than for themselves
	if (!$session->canAccess($session::SECTION_USERS, $session::ACCESS_WRITE)) {
		unset($data['user_id'], $data['user']);
	}
	elseif (!$entry->user_id) {
		$data['user'] ??= [];
	}

	$entry->importForm($data);
	$entry->save();

	Utils::reloadParentFrameIfDialog();
}, $csrf_key);

$tasks = Tracking::listTasks();
$now = new Date;
$date = isset($_GET['date']);
$is_today = $date && $_GET['date'] === date('Y-m-d');
$submit_label = $is_today && !$entry->duration ? 'Démarrer le chrono' : 'Enregistrer';

$tpl->assign(compact('tasks', 'csrf_key', 'now', 'selected_user', 'entry', 'entry_duration', 'date', 'is_today', 'submit_label'));

$tpl->display(\Paheko\PLUGIN_ROOT . '/templates/edit.tpl');
