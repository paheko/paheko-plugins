CREATE TABLE IF NOT EXISTS plugin_pim_contacts (
	id INTEGER PRIMARY KEY NOT NULL,
	id_user INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
	uri TEXT NOT NULL,
	first_name TEXT NOT NULL,
	last_name TEXT NULL,
	title TEXT NULL,
	mobile_phone TEXT NULL,
	address TEXT NULL,
	phone TEXT NULL,
	email TEXT NULL,
	web TEXT NULL,
	notes TEXT NULL,
	birthday TEXT NULL CHECK (birthday IS NULL OR birthday = date(birthday)),
	photo TEXT NULL,
	raw TEXT NULL,
	updated TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (updated = datetime(updated)),
	archived INTEGER NOT NULL DEFAULT 0
);

CREATE UNIQUE INDEX IF NOT EXISTS plugin_pim_contacts_uri ON plugin_pim_contacts (id_user, uri);
CREATE INDEX IF NOT EXISTS plugin_pim_contacts_user ON plugin_pim_contacts (id_user);

CREATE TABLE IF NOT EXISTS plugin_pim_events_categories (
	id INTEGER NOT NULL PRIMARY KEY,
	id_user INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
	title TEXT NOT NULL,
	default_reminder INTEGER NOT NULL DEFAULT 0,
	color INTEGER NULL,
	is_default INTEGER DEFAULT 0
);

CREATE INDEX IF NOT EXISTS plugin_pim_events_categories_user ON plugin_pim_events_categories (id_user);


CREATE TABLE IF NOT EXISTS plugin_pim_events (
	id INTEGER PRIMARY KEY NOT NULL,
	id_user INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
	id_category INTEGER NULL REFERENCES plugin_pim_events_categories (id) ON DELETE CASCADE,
	uri TEXT NOT NULL,
	title TEXT NOT NULL,
	start TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (start = datetime(start)),
	end TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (end = datetime(end)),
	all_day INTEGER NOT NULL DEFAULT 0,
	timezone TEXT NOT NULL,
	desc TEXT NULL,
	location TEXT NULL,
	reminder INTEGER NOT NULL DEFAULT 0,
	reminder_status INTEGER NOT NULL DEFAULT 0,
	raw TEXT NULL,
	updated TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (updated = datetime(updated))
);

CREATE INDEX IF NOT EXISTS plugin_pim_events_user ON plugin_pim_events (id_user);
CREATE UNIQUE INDEX IF NOT EXISTS plugin_pim_events_uri ON plugin_pim_events (uri);

CREATE TABLE IF NOT EXISTS plugin_pim_changes (
	id_user INTEGER NOT NULL,
	entity TEXT NOT NULL,
	uri TEXT NOT NULL,
	type INTEGER NOT NULL,
	date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (date = datetime(date))
);

CREATE INDEX IF NOT EXISTS plugin_pim_changes_table ON plugin_pim_changes (id_user, entity);
