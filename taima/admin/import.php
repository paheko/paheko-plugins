<?php

namespace Paheko\Plugin\Taima;

use Paheko\Plugin\Taima\Tracking;
use Paheko\CSV_Custom;
use Paheko\Utils;
use Paheko\UserException;

use function Paheko\{f, qg};

$session->requireAccess($session::SECTION_USERS, $session::ACCESS_ADMIN);

require_once __DIR__ . '/_inc.php';

$csrf_key = 'taima_import';
$csv = new CSV_Custom($session, 'taima_import');
$tasks = Tracking::listTasks();

$columns = [
	'task'           => 'Catégorie',
	'project'        => 'Projet',
	'title'          => 'Titre',
	'notes'          => 'Notes',
	'fullname'       => 'Nom et prénom du membre',
	'name'           => 'Nom du membre',
	'surname'        => 'Prénom du membre',
	'date'           => 'Date',
	'duration_hours' => 'Durée, en heures',
	'duration'       => 'Durée, en minutes',
];

$benevalibre_match = [
	'name' => 'Nom',
	'surname' => 'Prénom',
	'duration_hours' => 'Heures, en décimal',
	'task' => 'Catégorie',
	'title' => 'Titre',
	'notes' => 'Description',
];

$csv->setColumns($columns);
$csv->setMandatoryColumns(['date']);

// Detect Bénévalibre files
if ($csv->loaded() && $csv->hasRawHeaderColumn('Heures, en décimal')) {
	$csv->setColumns($columns, $benevalibre_match);
}

$form->runIf('cancel', function () use ($csv) {
	$csv->clear();
}, $csrf_key, Utils::getSelfURI(null));

$form->runIf(f('load') && isset($_FILES['file']['tmp_name']), function () use ($csv, $columns, $benevalibre_match) {
	$csv->upload($_FILES['file']);
}, $csrf_key, Utils::getSelfURI());

$form->runIf(f('set_translation_table') && $csv->loaded(), function () use (&$csv) {
	$csv->skip((int)f('skip_first_line'));
	$csv->setTranslationTableAuto();

	$table = $csv->getTranslationTable();

	if (!in_array('duration', $table) && !in_array('duration_hours', $table)) {
		throw new UserException('Aucune colonne indiquant la durée n\'a été sélectionnée');
	}
}, $csrf_key);

$form->runIf('import', function () use (&$csv) {
	Tracking::saveImport($csv, (array)$_POST['categories']);
	$csv->clear();
}, $csrf_key, Utils::getSelfURI() . '?msg=OK');

$categories = null;
$rows = null;
$categories_match = $_POST['categories'] ?? null;

if ($csv->ready() && null === $categories_match) {
	$categories = Tracking::findImportCategories($csv, $tasks);
}

if ($csv->ready() && $categories_match) {
	$rows = Tracking::createImport($csv, (array)$categories_match);
}

$tpl->assign(compact('csrf_key', 'csv', 'categories', 'categories_match', 'rows', 'tasks'));

$tpl->display(__DIR__ . '/../templates/import.tpl');
