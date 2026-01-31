DROP TABLE IF EXISTS plugin_usermap_locations;

CREATE TABLE IF NOT EXISTS plugin_usermap_locations (
	id_user INTEGER NOT NULL PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
	address_hash TEXT NOT NULL,
	lat REAL NULL,
	lon REAL NULL
);

CREATE INDEX IF NOT EXISTS plugin_usermap_locations_hash ON plugin_usermap_locations (id_user, address_hash);
