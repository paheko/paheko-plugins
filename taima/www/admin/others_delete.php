<?php

namespace Garradin\Plugin\Taima;

use Garradin\Plugin\Taima\Tracking;
use Garradin\Plugin\Taima\Entities\Entry;
use Garradin\Membres;
use Garradin\Utils;
use Garradin\UserException;

use function Garradin\{f, qg};

$session->requireAccess($session::SECTION_USERS, $session::ACCESS_ADMIN);

require_once __DIR__ . '/_inc.php';

$csrf_key = 'remove_task';
$entry = Tracking::get((int)qg('id'));

if (!$entry) {
	throw new UserException('Tâche inconnue');
}

$form->runIf('delete', function ()  use ($entry) {
	$entry->delete();
}, $csrf_key, Utils::getSelfURI(['ok' => 1]));

$tpl->assign(compact('csrf_key'));

$tpl->display(\Garradin\PLUGIN_ROOT . '/templates/others_delete.tpl');
