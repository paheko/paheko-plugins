<?php

namespace Paheko\Plugin\Taima;

use Paheko\Plugin\Taima\Tracking;
use Paheko\Plugin\Taima\Entities\Entry;
use Paheko\Membres;
use Paheko\Utils;
use Paheko\UserException;

use function Paheko\{f, qg};

require_once __DIR__ . '/_inc.php';

$csrf_key = 'remove_task';
$entry = Tracking::get((int)qg('id'));

if (!$entry) {
	throw new UserException('Tâche inconnue');
}

if (!$session->canAccess($session::SECTION_USERS, $session::ACCESS_WRITE)
	&& (!$session->isLogged() || $entry->user_id !== $session::getUserId())) {
	throw new UserException('Vous n\'avez pas accès à cette tâche');
}

$form->runIf('delete', function ()  use ($entry) {
	$entry->delete();
}, $csrf_key, Utils::getSelfURI(['ok' => 1]));

$tpl->assign(compact('csrf_key'));

$tpl->display(\Paheko\PLUGIN_ROOT . '/templates/delete.tpl');
