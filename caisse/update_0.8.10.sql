-- Rename position column to is_default
ALTER TABLE @PREFIX_methods ADD COLUMN is_default INTEGER NOT NULL DEFAULT 0;

UPDATE @PREFIX_methods SET is_default = 1 WHERE position = 1 LIMIT 1;

ALTER TABLE @PREFIX_methods DROP COLUMN position;
