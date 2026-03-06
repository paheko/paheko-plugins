ALTER TABLE @PREFIX_tabs_items RENAME TO @PREFIX_tabs_items_old;

CREATE TABLE IF NOT EXISTS @PREFIX_tabs_items (
	-- Items in a customer tab
	id INTEGER NOT NULL PRIMARY KEY,
	tab INTEGER NOT NULL REFERENCES @PREFIX_tabs (id) ON DELETE CASCADE,
	added TEXT NOT NULL DEFAULT (datetime('now','localtime')),
	product INTEGER NULL REFERENCES @PREFIX_products (id) ON DELETE SET NULL,
	qty INTEGER NOT NULL,
	price INTEGER NOT NULL,
	total INTEGER NOT NULL,
	weight INTEGER NULL,
	name TEXT NOT NULL,
	category_name TEXT NOT NULL,
	description TEXT NULL,
	account TEXT NULL,
	type INTEGER NOT NULL DEFAULT 0,
	pricing INTEGER NOT NULL DEFAULT 0,
	id_fee INTEGER NULL REFERENCES services_fees (id) ON DELETE SET NULL,
	id_subscription INTEGER NULL REFERENCES services_subscriptions (id) ON DELETE SET NULL,
	id_parent_item INTEGER NULL REFERENCES @PREFIX_tabs_items (id) ON DELETE CASCADE,
	id_method INTEGER NULL REFERENCES @PREFIX_methods (id) ON DELETE RESTRICT
);

-- List of columns is required, as table order might be different (weird)
INSERT INTO @PREFIX_tabs_items SELECT
	id,
	tab,
	added,
	product,
	qty,
	price,
	total,
	weight,
	name,
	category_name,
	description,
	account,
	type,
	pricing,
	id_fee,
	id_subscription,
	id_parent_item,
	id_method
FROM @PREFIX_tabs_items_old;
DROP TABLE @PREFIX_tabs_items_old;

CREATE INDEX IF NOT EXISTS @PREFIX_tabs_items_tab ON @PREFIX_tabs_items (tab);
CREATE INDEX IF NOT EXISTS @PREFIX_tabs_items_type_tab ON @PREFIX_tabs_items(type, tab);
