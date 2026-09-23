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
$session = Session::getInstance();
$date = Form::getQueryString('date');

if ($id = Form::getQueryInt('id')) {
	$entry = Tracking::get($id);

	if ($entry->id_user !== $session::getUserId()) {
		throw new UserException('Vous n\'avez pas accès à cette entrée', 403);
	}
}
// Create new entry
else {
	$entry = new Entry;
	$entry->set('user_id', $session::getUserId());
	$entry->importForm(compact('date'));
}

$entry_duration = isset($entry->duration) ? Tracking::formatMinutes($entry->duration) : null;

$form->runIf('save', function () use ($entry, $session) {
	$data = $_POST;
	unset($data['user_id']);
	$entry->importForm($data);
	$entry->save();
}, $csrf_key, '!p/taima/edit_me.php?ok');

$tasks = Tracking::listTasks();
$now = new Date;
$date = isset($_GET['date']);
$is_today = $date && $_GET['date'] === date('Y-m-d');
$duration_required = !($is_today && !$entry->duration);
$submit_label = !$duration_required ? 'Démarrer le chrono' : 'Enregistrer';

$tpl->assign(compact('tasks', 'csrf_key', 'now', 'entry', 'entry_duration', 'duration_required', 'date', 'submit_label'));

$tpl->display(\Paheko\PLUGIN_ROOT . '/templates/edit_me.tpl');
