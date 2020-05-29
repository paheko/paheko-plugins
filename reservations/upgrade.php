<?php

namespace Garradin;

$old_version = $plugin->getInfos('version');
$db = DB::getInstance();
$config = Config::getInstance();

if (version_compare($old_version, '0.5', '<')) {
	$db->toggleForeignKeys(false);

	$db->exec(sprintf('
	ALTER TABLE plugin_reservations_creneaux RENAME TO plugin_reservations_creneaux_old;
	ALTER TABLE plugin_reservations_personnes RENAME TO plugin_reservations_personnes_old;
	DROP INDEX prp_reservation_nom;

	CREATE TABLE IF NOT EXISTS plugin_reservations_categories
	(
		id INTEGER NOT NULL PRIMARY KEY,
		nom TEXT NOT NULL,
		introduction TEXT NULL,
		description TEXT NULL,
		champ TEXT NULL
	);

	CREATE TABLE IF NOT EXISTS plugin_reservations_creneaux
	(
		id INTEGER NOT NULL PRIMARY KEY, -- Numéro unique
		categorie INTEGER NOT NULL REFERENCES plugin_reservations_categories(id) ON DELETE CASCADE,
		jour TEXT NOT NULL,
		heure TEXT NOT NULL,
		repetition TEXT NOT NULL,
		maximum INT NOT NULL
	);

	CREATE TABLE IF NOT EXISTS plugin_reservations_personnes
	(
		id INTEGER NOT NULL PRIMARY KEY,
		creneau INTEGER NOT NULL REFERENCES plugin_reservations_creneaux (id) ON DELETE CASCADE,
		date TEXT NOT NULL,
		nom NULL,
		champ NULL
	);

	CREATE UNIQUE INDEX IF NOT EXISTS prp_reservation_nom ON plugin_reservations_personnes (creneau, date, nom, champ);

	INSERT INTO plugin_reservations_categories (nom) VALUES (\'Réservations\');
	INSERT INTO plugin_reservations_creneaux SELECT id, 1, jour, heure, repetition, maximum FROM plugin_reservations_creneaux_old;

	INSERT INTO plugin_reservations_personnes SELECT id, creneau, date, CASE WHEN id_membre IS NOT NULL THEN (SELECT %s FROM membres WHERE id = id_membre) ELSE nom END, NULL FROM plugin_reservations_personnes_old;

	DROP TABLE plugin_reservations_creneaux_old;
	DROP TABLE plugin_reservations_personnes_old;', $config->get('champ_identite')));

	// Remise en place du texte
	$db->update('plugin_reservations_categories', ['description' => $plugin->getConfig('text')], 'id = 1');

	// Suppression config
	$db->update('plugins', ['config' => null], 'id = :id', ['id' => $plugin->id]);
}