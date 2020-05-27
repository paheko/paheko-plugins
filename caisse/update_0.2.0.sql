ALTER TABLE @PREFIX_methods RENAME TO @PREFIX_methods_old;
ALTER TABLE @PREFIX_sessions RENAME TO @PREFIX_sessions_old;
ALTER TABLE @PREFIX_tabs_items RENAME TO @PREFIX_tabs_items_old;
ALTER TABLE @PREFIX_tabs_payments RENAME TO @PREFIX_tabs_payments_old;

CREATE TABLE IF NOT EXISTS @PREFIX_methods (
	-- Payment methods
	id INTEGER NOT NULL PRIMARY KEY,
	name TEXT NOT NULL,
	is_cash INTEGER NOT NULL DEFAULT 0,
	min INTEGER NULL,
	max INTEGER NULL
);

CREATE TABLE IF NOT EXISTS @PREFIX_sessions (
	-- Cash register sessions
	id INTEGER NOT NULL PRIMARY KEY,
	opened TEXT NOT NULL DEFAULT (datetime('now','localtime')),
	closed TEXT NULL,
	open_user INTEGER NULL,
	open_amount INTEGER NULL,
	close_amount INTEGER NULL,
	close_user INTEGER NULL,
	error_amount INTEGER NULL
);

CREATE TABLE IF NOT EXISTS @PREFIX_tabs_items (
	-- Items in a customer tab
	id INTEGER NOT NULL PRIMARY KEY,
	tab INTEGER NOT NULL REFERENCES @PREFIX_tabs (id) ON DELETE CASCADE,
	added TEXT NOT NULL DEFAULT (datetime('now','localtime')),
	product INTEGER NULL REFERENCES @PREFIX_products (id) ON DELETE SET NULL,
	qty INTEGER NOT NULL,
	price INTEGER NOT NULL,
	name TEXT NOT NULL,
	category_name TEXT NOT NULL,
	description TEXT NULL
);

CREATE TABLE IF NOT EXISTS @PREFIX_tabs_payments (
	-- Payments for a tab
	id INTEGER NOT NULL PRIMARY KEY,
	tab INTEGER NOT NULL REFERENCES @PREFIX_tabs (id) ON DELETE CASCADE,
	method INTEGER NULL REFERENCES @PREFIX_methods (id) ON DELETE RESTRICT,
	date TEXT NOT NULL DEFAULT (datetime('now','localtime')),
	amount INTEGER NOT NULL, -- Can be negative for a refund
	reference TEXT NULL
);

-- Fill new column "is_cash"
INSERT INTO @PREFIX_methods SELECT id, name, CASE WHEN name = "Espèces" THEN 1 ELSE 0 END, min, max FROM @PREFIX_methods_old;

-- Fill new column "close_user"
INSERT INTO @PREFIX_sessions SELECT *, CASE WHEN closed IS NOT NULL THEN open_user ELSE NULL END, NULL FROM @PREFIX_sessions_old;

-- Fill tabs items with products names and descriptions, for archival purposes
INSERT INTO @PREFIX_tabs_items
	SELECT ti.id,
		ti.tab,
		ti.added,
		ti.product,
		ti.qty,
		ti.price,
		p.name,
		c.name,
		p.description
	FROM @PREFIX_tabs_items_old ti
	INNER JOIN @PREFIX_tabs t ON t.id = ti.tab
	INNER JOIN @PREFIX_sessions s ON s.id = t.session
	INNER JOIN @PREFIX_products p ON p.id = ti.product
	INNER JOIN @PREFIX_categories c ON c.id = p.category
	GROUP BY ti.id;

INSERT INTO @PREFIX_tabs_payments SELECT * FROM @PREFIX_tabs_payments_old;

DROP TABLE @PREFIX_methods_old;
DROP TABLE @PREFIX_sessions_old;
DROP TABLE @PREFIX_tabs_items_old;
DROP TABLE @PREFIX_tabs_payments_old;

UPDATE @PREFIX_methods SET min = 0 WHERE id = 3;