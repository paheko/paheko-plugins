<?php

use Paheko\Plugin\Shortcuts\Shortcuts;

use Paheko\DB;

$db = DB::getInstance();
$db->exec('
CREATE TABLE IF NOT EXISTS plugin_shortcuts_shortcuts (
	id INTEGER NOT NULL PRIMARY KEY,
	label TEXT NOT NULL,
	url TEXT NOT NULL,
	shape TEXT NULL,
	restrict_section TEXT NULL,
	restrict_level TEXT NULL,
	icon INTEGER NOT NULL DEFAULT 0,
	sort_order INTEGER NOT NULL DEFAULT 0,
	home INTEGER NOT NULL DEFAULT 0,
	menu INTEGER NOT NULL DEFAULT 0,
	iframe INTEGER NOT NULL DEFAULT 0
);
');

$plugin->registerSignal('home.button', [Shortcuts::class, 'homeButtons']);
$plugin->registerSignal('menu.item', [Shortcuts::class, 'menuItems']);
