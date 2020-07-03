-- Add missing primary key
ALTER TABLE @PREFIX_products_methods RENAME TO @PREFIX_products_methods_old;

CREATE TABLE IF NOT EXISTS @PREFIX_products_methods (
	-- Link between products and available payment methods
	product INTEGER NOT NULL REFERENCES @PREFIX_products (id) ON DELETE CASCADE,
	method INTEGER NOT NULL REFERENCES @PREFIX_methods (id) ON DELETE CASCADE,
	PRIMARY KEY(product, method)
);

INSERT INTO @PREFIX_products_methods SELECT * FROM @PREFIX_products_methods_old;

DROP TABLE @PREFIX_products_methods_old;

-- Add user_id column
ALTER TABLE @PREFIX_tabs RENAME TO @PREFIX_tabs_old;

CREATE TABLE IF NOT EXISTS @PREFIX_tabs (
	-- Customer tabs (or carts)
	id INTEGER NOT NULL PRIMARY KEY,
	session INTEGER NOT NULL REFERENCES @PREFIX_sessions (id) ON DELETE CASCADE,
	name TEXT NULL,
	user_id INTEGER NULL,
	opened TEXT NOT NULL DEFAULT (datetime('now','localtime')),
	closed TEXT NULL -- If NULL it is still open
);

INSERT INTO @PREFIX_tabs SELECT id, session, name, NULL, opened, closed FROM @PREFIX_tabs_old;

DROP TABLE @PREFIX_tabs_old;

-- Add account columns
ALTER TABLE @PREFIX_categories ADD COLUMN account TEXT NULL;
ALTER TABLE @PREFIX_tabs_items ADD COLUMN account TEXT NULL;

-- Set accounts for categories
UPDATE @PREFIX_categories SET account = '756' WHERE name = 'Adhésion';
UPDATE @PREFIX_categories SET account = '706' WHERE name = 'Forfaits coup de pouce vélo';
UPDATE @PREFIX_categories SET account = '707' WHERE name = 'Pièces neuves';
UPDATE @PREFIX_categories SET account = '708A' WHERE name = 'Pièces d''occasion';
UPDATE @PREFIX_categories SET account = '7587' WHERE name = 'Vélo d''occasion';

-- Set account for old tab items
UPDATE @PREFIX_tabs_items SET account = (SELECT c.account FROM @PREFIX_categories c
	INNER JOIN @PREFIX_products p ON p.category = c.id WHERE @PREFIX_tabs_items.product = p.id);

-- Add new category and products for donations
INSERT INTO @PREFIX_categories (id, name, account) VALUES (6, 'Dons et soutiens', '754');

INSERT INTO @PREFIX_products (category, name, price) VALUES
	(6, "Don", 500),
	(6, "Don", 1000),
	(6, "Don", 1500);

-- Add new products
INSERT INTO @PREFIX_products (category, name, price) VALUES
	(3, "Chaîne 9v", 1500),
	(3, "Chaîne 10v", 2000),
	(3, 'Roue arrière 700C', 3000),
	(4, 'Pédales (paire)', 200);

-- Drop 8-9 speed chains
DELETE FROM @PREFIX_products WHERE name = 'Chaîne 8-9v';

-- Add payment methods
INSERT OR IGNORE INTO @PREFIX_products_methods
	SELECT p.id, m.id FROM @PREFIX_products p
	CROSS JOIN @PREFIX_methods m
	WHERE p.name IN ('Don', 'Chaîne 9v', 'Chaîne 10v', 'Roue arrière 700C',
		-- Produits qui n'étaient pas encore coup de pouce vélo
		'Pédales (paire)', 'Garde-boue', 'Moyeu', 'Jante (nue)', 'Sonnette',
		'Guidoline tissu noir', 'Adaptateur (tige de selle, potence)', 'Béquille');
