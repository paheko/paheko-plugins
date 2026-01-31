-- Set non-existing users to NULL before creating foreign key
UPDATE @PREFIX_tabs SET user_id = NULL WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM users);

ALTER TABLE @PREFIX_tabs RENAME TO @PREFIX_tabs_old;

CREATE TABLE IF NOT EXISTS @PREFIX_tabs (
	-- Customer tabs (or carts)
	id INTEGER NOT NULL PRIMARY KEY,
	session INTEGER NOT NULL REFERENCES @PREFIX_sessions (id) ON DELETE CASCADE,
	name TEXT NULL,
	user_id INTEGER NULL REFERENCES users (id) ON DELETE SET NULL,
	opened TEXT NOT NULL DEFAULT (datetime('now','localtime')),
	closed TEXT NULL -- If NULL it is still open
);

INSERT INTO @PREFIX_tabs SELECT * FROM @PREFIX_tabs_old;

DROP TABLE @PREFIX_tabs_old;

CREATE INDEX IF NOT EXISTS @PREFIX_tabs_session ON @PREFIX_tabs (session);
