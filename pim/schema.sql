CREATE TABLE IF NOT EXISTS plugin_pim_contacts (
	id INTEGER PRIMARY KEY NOT NULL,
	id_user INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
	uri TEXT NOT NULL DEFAULT (hex(randomblob(8))),
	first_name TEXT NULL,
	last_name TEXT NULL,
	title TEXT NULL,
	phone TEXT NULL,
	email TEXT NULL,
	birthday TEXT NULL CHECK (birthday IS NULL OR birthday = date(birthday)),
	photo BLOB NULL,
	raw TEXT NULL,
	updated TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (updated = datetime(updated)),
	archived INTEGER NOT NULL DEFAULT 0,
	CHECK first_name IS NOT NULL OR last_name IS NOT NULL
);

CREATE UNIQUE INDEX IF NOT EXISTS plugin_pim_contacts_uri ON plugin_pim_contacts (uri);

CREATE TABLE IF NOT EXISTS plugin_pim_events (
	id INTEGER PRIMARY KEY NOT NULL,
	id_user INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
	id_category INTEGER NULL REFERENCES events_categories (id) ON DELETE SET NULL,
	uri TEXT NOT NULL DEFAULT (hex(randomblob(8))),
	title TEXT NOT NULL,
	date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (date = datetime(date)),
	date_end TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (date_end = datetime(date_end)),
	all_day INT NOT NULL DEFAULT 0,
	timezone TEXT NOT NULL,
	desc TEXT NULL,
	location TEXT NULL,
	reminder INT NOT NULL DEFAULT 0,
	reminder_status INT NOT NULL DEFAULT 0,
	raw TEXT NULL,
	updated TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (updated = datetime(updated))
);

CREATE UNIQUE INDEX IF NOT EXISTS plugin_pim_events_uri ON plugin_pim_events (uri);

CREATE TABLE IF NOT EXISTS plugin_pim_events_categories (
	id INTEGER NOT NULL PRIMARY KEY,
	id_user INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
	title TEXT NOT NULL,
	default_reminder INT NOT NULL DEFAULT 0,
	color INT NULL,
	is_default INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS plugin_pim_changes (
	entity TEXT NOT NULL,
	uri TEXT NOT NULL,
	type INTEGER NOT NULL,
	timestamp TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (timestamp = datetime(timestamp)),
);

CREATE INDEX IF NOT EXISTS plugin_pim_changes_table ON plugin_pim_changes (table);
