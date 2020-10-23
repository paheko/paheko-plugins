-- Fix issues with non-existing products after 0.3.0 update
DELETE FROM @PREFIX_products_methods WHERE product NOT IN (SELECT id FROM @PREFIX_products);
UPDATE @PREFIX_tabs_items SET product = NULL WHERE product NOT IN (SELECT id FROM @PREFIX_products);

ALTER TABLE @PREFIX_methods ADD COLUMN account TEXT NULL;
ALTER TABLE @PREFIX_tabs_payments ADD COLUMN account TEXT NULL;

UPDATE @PREFIX_methods SET account = '530' WHERE id = 1;
UPDATE @PREFIX_methods SET account = '5112' WHERE id = 2;
UPDATE @PREFIX_methods SET account = '511A' WHERE id = 3;

UPDATE @PREFIX_tabs_payments SET account = '530' WHERE method = 1;
UPDATE @PREFIX_tabs_payments SET account = '5112' WHERE method = 2;
UPDATE @PREFIX_tabs_payments SET account = '4687A' WHERE method = 3;