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
$allow_multiple_users = false;

$session->requireAccess($session::SECTION_USERS, $session::ACCESS_WRITE);

// Duplicate from ID
if ($id = Form::getQueryInt('copy')) {
	$entry = Tracking::get($id);
	$entry = clone $entry;
}
// Edit existing entry
elseif ($id = Form::getQueryInt('id')) {
	$entry = Tracking::get($id);
}
// Create new entry
else {
	$entry = new Entry;
}

if ($entry->exists()) {
	$allow_multiple_users = false;
}
else {
	$allow_multiple_users = true;

	if ($id_user = Form::getQueryInt('id_user')) {
		$user = Users::get($id_user);

		if (!$user) {
			throw new UserException('Membre inconnu');
		}

		$entry->set('user_id', $user->id);
	}
}

$selected_users = $entry->user_id ? [$entry->user_id => $entry->user_name()] : null;
$entry_duration = isset($entry->duration) ? Tracking::formatMinutes($entry->duration) : null;

$form->runIf('save', function () use ($entry, $session) {
	$entry->importForm();
	$users = Form::getPostArray('users');

	if (!$entry->exists() && is_array($users) && count($users)) {
		foreach ($users as $id => $name) {
			$entry = clone $entry;
			$entry->set('user_id', $id);
			$entry->save();
		}
	}
	else {
		$entry->set('user_id', $users ? key($users) : null);
		$entry->save();
	}
}, $csrf_key, '!p/taima/all.php');

$tasks = Tracking::listTasks();
$now = new Date;

$tpl->assign(compact('tasks', 'csrf_key', 'now', 'selected_users', 'allow_multiple_users', 'entry', 'entry_duration'));

$tpl->display(\Paheko\PLUGIN_ROOT . '/templates/edit.tpl');
