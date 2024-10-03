ALTER TABLE @PREFIX_tabs_items ADD COLUMN total INTEGER NOT NULL DEFAULT 0;
UPDATE @PREFIX_tabs_items SET total = qty * price;
