DROP TABLE IF EXISTS plugin_taima_tasks;
DROP TABLE IF EXISTS plugin_taima_entries;

CREATE TABLE IF NOT EXISTS plugin_taima_tasks (
	id INTEGER NOT NULL PRIMARY KEY,
	label TEXT NOT NULL,
	value INTEGER NULL,
	account TEXT NULL,
	id_project INTEGER NULL
);

CREATE TABLE IF NOT EXISTS plugin_taima_entries (
	id INTEGER NOT NULL PRIMARY KEY,
	user_id INTEGER NULL REFERENCES users (id) ON DELETE SET NULL,
	task_id INTEGER NULL REFERENCES plugin_taima_tasks(id) ON DELETE SET NULL,
	year INTEGER NOT NULL CHECK (LENGTH(year) = 4),
	week INTEGER NOT NULL CHECK (week >= 1 AND week <= 53),
	date TEXT NOT NULL,
	notes TEXT,
	duration INTEGER NULL, -- duration of timer, in minutes
	timer_started INTEGER NULL -- date time for the start of the timer, is null if no timer is running
);

INSERT INTO plugin_taima_tasks (label, value, account) VALUES
	('Comptabilité', 2500, 864),
	('Administratif', 2500, 864),
	('Communication', 2500, 864);
