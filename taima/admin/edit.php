<?php

namespace Paheko\Plugin\Taima;

use Paheko\Plugin\Taima\Tracking;
use Paheko\Plugin\Taima\Entities\Entry;
use Paheko\Users\Session;
use Paheko\Users\Users;
use Paheko\Utils;
use Paheko\UserException;

use KD2\DB\Date;
use KD2\Form as Form;

require_once __DIR__ . '/_inc.php';

$csrf_key = 'edit_task';
$selected_users = null;
$session = Session::getInstance();

// Duplicate from ID
if ($id = Form::getQueryInt('copy')) {
	$entry = Tracking::get($id);
	$entry = clone $entry;

	// If user doesn't have write access, reset user_id
	if (!$session->canAccess($session::SECTION_USERS, $session::ACCESS_WRITE)) {
		$entry->set('user_id', null);
	}
}
// Edit existing entry
elseif ($id = Form::getQueryInt('id')) {
	$entry = Tracking::get($id);
}
// Create new entry
else {
	$entry = new Entry;

	// If date is set, it means it's for the current user
	if ($date = Form::getQueryString('date')) {
		$entry->set('user_id', $session::getUserId());
		$entry->importForm(compact('date'));
	}
}

if ($entry->exists()) {
	// users with write_access can edit entries for anyone
	// others can only edit their own entries
	if (!$session->canAccess($session::SECTION_USERS, $session::ACCESS_WRITE)
		&& $entry->user_id !== $session::getUserId()) {
		throw new UserException('Vous n\'avez pas accès à cette tâche');
	}

	$allow_multiple_users = false;
}
else {
	if ($session->canAccess($session::SECTION_USERS, $session::ACCESS_WRITE)) {
		$allow_multiple_users = true;

		if ($id_user = Form::getQueryInt('id_user')) {
			$user = Users::get($id_user);

			if (!$user) {
				throw new UserException('Membre inconnu');
			}

			$entry->set('user_id', $user->id);
		}
	}
	else {
		$entry->set('user_id', $session::getUserId());
		$allow_multiple_users = false;
	}
}

$selected_users = $entry->user_id ? [$entry->user_id => $entry->user_name()] : null;
$entry_duration = isset($entry->duration) ? Tracking::formatMinutes($entry->duration) : null;

$form->runIf('save', function () use ($entry, $session) {
	$data = $_POST;
	unset($data['user_id']);
	$entry->importForm($data);

	// Users who can only create entries cannot create entries other than for themselves
	if (!$entry->exists()
		&& $session->canAccess($session::SECTION_USERS, $session::ACCESS_WRITE)) {
		$users = Form::getPostArray('users');

		if (!$entry->user_id && count($users)) {
			foreach ($users as $id => $name) {
				$entry = clone $entry;
				$entry->set('user_id', $id);
				$entry->save();
			}

			Utils::redirect('!p/taima/all.php');
		}
		else {
			// User is NULL
			$entry->save();
		}
	}
	else {
		// Existing entry
		$entry->save();
	}

	Utils::reloadParentFrameIfDialog('!p/taima/');
}, $csrf_key);

$tasks = Tracking::listTasks();
$now = new Date;
$date = isset($_GET['date']);
$is_today = $date && $_GET['date'] === date('Y-m-d');
$submit_label = $is_today && !$entry->duration ? 'Démarrer le chrono' : 'Enregistrer';

$tpl->assign(compact('tasks', 'csrf_key', 'now', 'selected_users', 'allow_multiple_users', 'entry', 'entry_duration', 'date', 'is_today', 'submit_label'));

$tpl->display(\Paheko\PLUGIN_ROOT . '/templates/edit.tpl');
