ALTER TABLE @PREFIX_methods ADD COLUMN position INTEGER NULL;

-- Set cash to be in first position
UPDATE @PREFIX_methods SET position = 1 WHERE type = 1 LIMIT 1;
