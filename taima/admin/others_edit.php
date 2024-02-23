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

$session->requireAccess($session::SECTION_USERS, $session::ACCESS_ADMIN);

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
}

$form->runIf('save', function () use ($entry) {
	$entry->setDateString(f('date'));
	$entry->user_id = Form::getSelectorValue(f('user'));
	$entry->importForm();
	$entry->setDuration(f('duration'));
	$entry->save();
}, $csrf_key, Utils::getSelfURI(['ok' => 1]));

$tasks = ['' => '--'] + Tracking::listTasks();
$now = new Date;

$tpl->assign(compact('tasks', 'csrf_key', 'now', 'selected_user', 'entry', 'entry_duration'));

$tpl->display(\Paheko\PLUGIN_ROOT . '/templates/others_edit.tpl');
