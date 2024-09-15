ALTER TABLE @PREFIX_methods RENAME TO @PREFIX_methods_old;

CREATE TABLE IF NOT EXISTS @PREFIX_methods (
	-- Payment methods
	id INTEGER NOT NULL PRIMARY KEY,
	name TEXT NOT NULL,
	type INTEGER NOT NULL DEFAULT 0, -- If "1" then no reference will be asked, if "0" then a reference can be attached, and the payment needs to be checked when closing the register
	min INTEGER NULL, -- Minimum amount that can be paid using this method
	max INTEGER NULL, -- Maximum amount that can be paid using this method
	account TEXT NULL, -- Accounting account code
	enabled INTEGER NOT NULL DEFAULT 1
);

INSERT INTO @PREFIX_methods SELECT * FROM @PREFIX_methods_old;
DROP TABLE @PREFIX_methods_old;

ALTER TABLE @PREFIX_tabs_items ADD COLUMN type INTEGER NOT NULL DEFAULT 0;
ALTER TABLE @PREFIX_tabs_payments ADD COLUMN status INTEGER NOT NULL DEFAULT 1;
ALTER TABLE @PREFIX_products ADD COLUMN archived INTEGER NOT NULL DEFAULT 0;

INSERT INTO @PREFIX_methods (name, type, account) VALUES ('Ardoise', 2, '4110');
INSERT INTO @PREFIX_products_methods (product, method) SELECT id, (SELECT id FROM @PREFIX_methods WHERE name = 'Ardoise') FROM @PREFIX_products;
