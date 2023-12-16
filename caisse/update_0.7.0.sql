CREATE TABLE IF NOT EXISTS plugin_pos_weight_changes_types (
	id INTEGER NOT NULL PRIMARY KEY
);

CREATE TABLE IF NOT EXISTS @PREFIX_categories_weight_history (
	id INTEGER NOT NULL PRIMARY KEY,
	category INTEGER NOT NULL REFERENCES @PREFIX_categories (id) ON DELETE CASCADE,
	change INTEGER NULL,
	date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Date of change
	item INTEGER NULL REFERENCES @PREFIX_tabs_items (id) ON DELETE CASCADE, -- Link to item in a customer tab
	type INTEGER NULL REFERENCES @PREFIX_weight_changes_types (id) ON DELETE CASCADE -- NULL = sale
);

ALTER TABLE @PREFIX_products RENAME TO @PREFIX_products_old;


CREATE TABLE IF NOT EXISTS @PREFIX_products (
	-- Products
	id INTEGER NOT NULL PRIMARY KEY,
	category INTEGER NOT NULL REFERENCES @PREFIX_categories (id) ON DELETE CASCADE,
	name TEXT NOT NULL,
	description TEXT NULL,
	price INTEGER NOT NULL,
	purchase_price INTEGER NULL,
	qty INTEGER NOT NULL DEFAULT 1, -- Default quantity when adding to cart
	stock INTEGER NULL, -- NULL if it's not subject to stock change (like a membership)
	weight INTEGER NULL,
	image TEXT NULL,
	code TEXT NULL
);

INSERT INTO @PREFIX_products SELECT id, category, name, description, price, NULL, qty, stock, NULL, image, NULL FROM @PREFIX_products_old;

DROP TABLE @PREFIX_products_old;

ALTER TABLE @PREFIX_tabs_items RENAME TO @PREFIX_tabs_items_old;

CREATE TABLE IF NOT EXISTS @PREFIX_tabs_items (
	-- Items in a customer tab
	id INTEGER NOT NULL PRIMARY KEY,
	tab INTEGER NOT NULL REFERENCES @PREFIX_tabs (id) ON DELETE CASCADE,
	added TEXT NOT NULL DEFAULT (datetime('now','localtime')),
	product INTEGER NULL REFERENCES @PREFIX_products (id) ON DELETE SET NULL,
	qty INTEGER NOT NULL,
	price INTEGER NOT NULL,
	weight INTEGER NULL,
	name TEXT NOT NULL,
	category_name TEXT NOT NULL,
	description TEXT NULL,
	account TEXT NULL
);

INSERT INTO @PREFIX_tabs_items SELECT id, tab, added, product, qty, price, NULL, name, category_name, description, account FROM @PREFIX_tabs_items_old;

DROP TABLE @PREFIX_tabs_items_old;;

CREATE INDEX IF NOT EXISTS @PREFIX_products_category ON @PREFIX_products (category);
CREATE INDEX IF NOT EXISTS @PREFIX_products_code ON @PREFIX_products (code);

CREATE INDEX IF NOT EXISTS @PREFIX_tabs_session ON @PREFIX_tabs (session);
CREATE INDEX IF NOT EXISTS @PREFIX_tabs_items_tab ON @PREFIX_tabs_items (tab);
CREATE INDEX IF NOT EXISTS @PREFIX_tabs_payments_tab ON @PREFIX_tabs_payments (tab);