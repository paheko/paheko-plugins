<?php

namespace Paheko\Plugin\Taima;

use Paheko\Plugin\Taima\Entities\Entry;
use Paheko\Plugin\Taima\Entities\Task;
use Paheko\Plugin\Taima\Tracking;

use Paheko\Users\DynamicFields;
use Paheko\DB;
use Paheko\Utils;
use Paheko\UserException;
use KD2\DB\EntityManager as EM;

use function Paheko\{f, qg};

$session->requireAccess($session::SECTION_USERS, $session::ACCESS_ADMIN);

$csrf_key = 'plugin_taima_import';
$url = Utils::plugin_url(['query' => '', 'file' => 'import.php']);

$add = null;
$links = null;
$tasks = [null => '--'] + DB::getInstance()->getAssoc('SELECT id, label FROM plugin_taima_tasks;');
$json = $session->get('taima_import_json');

if (isset($_GET['cancel'])) {
	$json = null;
	$session->set('taima_import_json', $json);
	$session->save();
}

$form->runIf('load', function () use (&$session) {
	$file = $_FILES['json'] ?? null;

	if (empty($file['size']) || empty($file['tmp_name']) || empty($file['name'])) {
		throw new UserException('Erreur à l\'envoi du fichier');
	}

	$json = json_decode(@file_get_contents($file['tmp_name']), true);

	if (empty($json) || !is_array($json)) {
		throw new UserException('Fichier JSON invalide');
	}

	$required = ['Heures, en décimal', 'Catégorie', 'Date', 'Niveau', 'Projet', 'Titre', 'Description'];

	foreach ($json as $l => $row) {
		foreach ($required as $name) {
			if (!array_key_exists($name, $row)) {
				throw new UserException(sprintf('Ligne %d: le champ "%s" est manquant.', $l+1, $name));
			}
		}
	}

	$session->set('taima_import_json', $json);
	$session->save();
}, $csrf_key, $url);

$links = f('links');

$form->runIf($json !== null && !$links, function () use ($json, &$links, $tasks) {
	$links = [];

	foreach ($json as $l => $row) {
		if ($row['Projet'] && !isset($links[$row['Projet']])) {
			$links[$row['Projet']] = array_search(trim((string) $row['Projet']), $tasks);
		}
		elseif ($row['Niveau'] && !isset($links[$row['Niveau']])) {
			$links[$row['Niveau']] = array_search(trim((string) $row['Niveau']), $tasks);
		}
		elseif ($row['Catégorie'] && !isset($links[$row['Catégorie']])) {
			$links[$row['Catégorie']] = array_search(trim((string) $row['Catégorie']), $tasks);
		}
	}

	ksort($links);
});

$form->runIf(f('preview') && $json && count($links), function () use ($json, &$add, &$links) {
	$add = [];

	$db = DB::getInstance();
	$id_field = DynamicFields::getNameFieldsSQL();

	foreach ($json as $l => $row) {
		$e = new Entry;
		$e->setDateString($row['Date']);
		$e->duration = intval($row['Heures, en décimal'] * 60);

		if (isset($row['Nom'], $row['Prénom'])) {
			$a = trim($row['Nom'] . ' ' . $row['Prénom']);
			$b = trim($row['Prénom'] . ' ' . $row['Nom']);
			$e->user_id = $db->firstColumn(sprintf('SELECT id FROM users WHERE %s = ? OR %1$s = ?;', $id_field), $a, $b) ?: null;
		}

		if ($row['Projet'] && isset($links[$row['Projet']])) {
			$e->task_id = (int)$links[$row['Projet']];
		}
		elseif ($row['Niveau'] && isset($links[$row['Niveau']])) {
			$e->task_id = (int)$links[$row['Niveau']];
		}
		elseif ($row['Catégorie'] && isset($links[$row['Catégorie']])) {
			$e->task_id = (int)$links[$row['Catégorie']];
		}

		$e->notes = ($row['Titre'] ?? '') . "\n" . ($row['Description'] ?? '');

		if (!$e->user_id && isset($row['Nom'], $row['Prénom'])) {
			$prefix = sprintf("%s %s", $row['Nom'], $row['Prénom']);

			if (isset($row['Pseudo'])) {
				$prefix .= sprintf('(%s)', $row['Pseudo']);
			}

			$e->notes = $prefix . "\n" . $e->notes;
		}

		$e->notes = trim($e->notes);

		if (empty($e->notes)) {
			$e->notes = null;
		}

		$add[] = ['entry' => $e] + $row;
	}
}, $csrf_key);

$form->runIf('save', function () use ($session) {
	$entries = Utils::array_transpose($_POST['entries'] ?? []);

	foreach ($entries as $entry) {
		$e = new Entry;
		$e->importForm($entry);
		$e->setDateString($entry['date']);
		$e->save();
	}

	$session->set('taima_import_json', null);
	$session->save();
}, $csrf_key, $url . '?ok');

$tpl->assign(compact('csrf_key', 'add', 'tasks', 'links'));

$tpl->register_modifier('taima_minutes', [Tracking::class, 'formatMinutes']);
$tpl->display(__DIR__ . '/../templates/import.tpl');
