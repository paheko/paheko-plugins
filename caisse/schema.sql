-- Amounts are stored as integers, including cents, eg. 15.99€ will be stored as 1599

CREATE TABLE IF NOT EXISTS @PREFIX_categories (
	id INTEGER NOT NULL PRIMARY KEY,
	name TEXT NOT NULL,
	account TEXT NULL
);

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

CREATE INDEX IF NOT EXISTS @PREFIX_products_category ON @PREFIX_products (category);
CREATE INDEX IF NOT EXISTS @PREFIX_products_code ON @PREFIX_products (code);

CREATE TABLE IF NOT EXISTS @PREFIX_products_stock_history (
	-- History of stock changes for a product
	id INTEGER NOT NULL PRIMARY KEY,
	product INTEGER NOT NULL REFERENCES @PREFIX_products (id) ON DELETE CASCADE,
	change INTEGER NOT NULL, -- Number of items removed or added to stock: can be negative or positive
	date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Date of change
	item INTEGER NULL REFERENCES @PREFIX_tabs_items (id) ON DELETE CASCADE, -- Link to item in a customer tab
	event INTEGER NULL REFERENCES @PREFIX_stock_events (id) ON DELETE CASCADE, -- Link to stock event
	CHECK(item IS NOT NULL OR event IS NOT NULL)
);

CREATE TABLE IF NOT EXISTS @PREFIX_weight_changes_types (
	id INTEGER NOT NULL PRIMARY KEY
);

CREATE TABLE IF NOT EXISTS @PREFIX_categories_weight_history (
	-- History of weight changes in a category
	id INTEGER NOT NULL PRIMARY KEY,
	category INTEGER NOT NULL REFERENCES @PREFIX_categories (id) ON DELETE CASCADE,
	change INTEGER NULL,
	date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Date of change
	item INTEGER NULL REFERENCES @PREFIX_tabs_items (id) ON DELETE CASCADE, -- Link to item in a customer tab
	type INTEGER NULL REFERENCES @PREFIX_weight_changes_types (id) ON DELETE CASCADE -- NULL = sale
);

CREATE TABLE IF NOT EXISTS @PREFIX_stock_events (
	-- Stock events (eg. delivery from supplier)
	id INTEGER NOT NULL PRIMARY KEY,
	date TEXT NOT NULL,
	type INTEGER NOT NULL,
	label TEXT NOT NULL,
	description TEXT NULL,
	applied INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS @PREFIX_methods (
	-- Payment methods
	id INTEGER NOT NULL PRIMARY KEY,
	name TEXT NOT NULL,
	is_cash INTEGER NOT NULL DEFAULT 0, -- If "1" then no reference will be asked, if "0" then a reference can be attached, and the payment needs to be checked when closing the register
	min INTEGER NULL, -- Minimum amount that can be paid using this method
	max INTEGER NULL, -- Maximum amount that can be paid using this method
	account TEXT NULL, -- Accounting account code
	enabled INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS @PREFIX_products_methods (
	-- Link between products and available payment methods
	product INTEGER NOT NULL REFERENCES @PREFIX_products (id) ON DELETE CASCADE,
	method INTEGER NOT NULL REFERENCES @PREFIX_methods (id) ON DELETE CASCADE,
	PRIMARY KEY(product, method)
);

CREATE TABLE IF NOT EXISTS @PREFIX_sessions (
	-- Cash register sessions
	id INTEGER NOT NULL PRIMARY KEY,
	opened TEXT NOT NULL DEFAULT (datetime('now','localtime')),
	closed TEXT NULL,
	open_user TEXT NULL,
	open_amount INTEGER NULL,
	close_amount INTEGER NULL,
	close_user TEXT NULL,
	error_amount INTEGER NULL
);

CREATE TABLE IF NOT EXISTS @PREFIX_tabs (
	-- Customer tabs (or carts)
	id INTEGER NOT NULL PRIMARY KEY,
	session INTEGER NOT NULL REFERENCES @PREFIX_sessions (id) ON DELETE CASCADE,
	name TEXT NULL,
	user_id INTEGER NULL,
	opened TEXT NOT NULL DEFAULT (datetime('now','localtime')),
	closed TEXT NULL -- If NULL it is still open
);

CREATE INDEX IF NOT EXISTS @PREFIX_tabs_session ON @PREFIX_tabs (session);

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

CREATE INDEX IF NOT EXISTS @PREFIX_tabs_items_tab ON @PREFIX_tabs_items (tab);

CREATE TABLE IF NOT EXISTS @PREFIX_tabs_payments (
	-- Payments for a tab
	id INTEGER NOT NULL PRIMARY KEY,
	tab INTEGER NOT NULL REFERENCES @PREFIX_tabs (id) ON DELETE CASCADE,
	method INTEGER NULL REFERENCES @PREFIX_methods (id) ON DELETE RESTRICT,
	date TEXT NOT NULL DEFAULT (datetime('now','localtime')),
	amount INTEGER NOT NULL, -- Can be negative for a refund
	reference TEXT NULL,
	account TEXT NULL
);

CREATE INDEX IF NOT EXISTS @PREFIX_tabs_payments_tab ON @PREFIX_tabs_payments (tab);

CREATE TRIGGER IF NOT EXISTS @PREFIX_tabs_account1 AFTER UPDATE ON @PREFIX_methods WHEN OLD.account != NEW.account
BEGIN
	UPDATE @PREFIX_tabs_payments SET account = NEW.account WHERE method = NEW.id;
END;

CREATE TRIGGER IF NOT EXISTS @PREFIX_tabs_account2 AFTER UPDATE ON @PREFIX_categories WHEN OLD.account != NEW.account
BEGIN
	UPDATE @PREFIX_tabs_items SET account = NEW.account WHERE product IN (SELECT id FROM @PREFIX_products WHERE category = NEW.id);
END;
