CREATE TRIGGER IF NOT EXISTS @PREFIX_tabs_account1 AFTER UPDATE ON @PREFIX_methods WHEN OLD.account != NEW.account
BEGIN
	UPDATE @PREFIX_tabs_payments SET account = NEW.account WHERE method = NEW.id;
END;

CREATE TRIGGER IF NOT EXISTS @PREFIX_tabs_account2 AFTER UPDATE ON @PREFIX_categories WHEN OLD.account != NEW.account
BEGIN
	UPDATE @PREFIX_tabs_items SET account = NEW.account WHERE product IN (SELECT id FROM @PREFIX_products WHERE category = NEW.id);
END;

UPDATE @PREFIX_methods SET account = account || '__' WHERE account IS NOT NULL;
UPDATE @PREFIX_categories SET account = account || '__' WHERE account IS NOT NULL;

UPDATE @PREFIX_methods SET account = REPLACE(account, '__', '') WHERE account IS NOT NULL;
UPDATE @PREFIX_categories SET account = REPLACE(account, '__', '') WHERE account IS NOT NULL;
