-- Rename position column to is_default
ALTER TABLE @PREFIX_methods ADD COLUMN is_default INTEGER NOT NULL DEFAULT 0;

UPDATE @PREFIX_methods SET is_default = 1 WHERE id = (SELECT id FROM @PREFIX_methods WHERE position = 1 LIMIT 1);

ALTER TABLE @PREFIX_methods RENAME TO @PREFIX_methods_old;

-- DROP COLUMN is not available until SQLite 3.35.0+
-- ALTER TABLE @PREFIX_methods DROP COLUMN position;
CREATE TABLE IF NOT EXISTS @PREFIX_methods (
	-- Payment methods
	id INTEGER NOT NULL PRIMARY KEY,
	id_location INTEGER NULL REFERENCES @PREFIX_locations (id) ON DELETE CASCADE,
	name TEXT NOT NULL,
	type INTEGER NOT NULL DEFAULT 0,
	min INTEGER NULL, -- Minimum amount that can be paid using this method
	max INTEGER NULL, -- Maximum amount that can be paid using this method
	account TEXT NULL, -- Accounting account code
	is_default INTEGER NOT NULL DEFAULT 0,
	enabled INTEGER NOT NULL DEFAULT 1
);

INSERT INTO @PREFIX_methods SELECT id, id_location, name, type, min, max, account, is_default, enabled FROM @PREFIX_methods_old;
DROP TABLE @PREFIX_methods_old;
