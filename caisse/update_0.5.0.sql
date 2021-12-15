DROP TABLE @PREFIX_stock_events;

CREATE TABLE IF NOT EXISTS @PREFIX_stock_events (
	-- Stock events (eg. delivery from supplier)
	id INTEGER NOT NULL PRIMARY KEY,
	date TEXT NOT NULL,
	label TEXT NOT NULL
);

ALTER TABLE @PREFIX_products_stock_history RENAME TO @PREFIX_products_stock_history_old;

CREATE TABLE IF NOT EXISTS @PREFIX_products_stock_history (
	-- History of stock changes for a product
	id INTEGER NOT NULL PRIMARY KEY,
	product INTEGER NOT NULL REFERENCES @PREFIX_products (id) ON DELETE CASCADE,
	change INTEGER NOT NULL, -- Number of items removed or added to stock: can be negative or positive
	date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Date of change
	item INTEGER NULL REFERENCES @PREFIX_tabs_items (id) ON DELETE CASCADE, -- Link to item in a customer tab
	event INTEGER NULL REFERENCES @PREFIX_stock_events (id) ON DELETE CASCADE -- Link to stock event
);

INSERT INTO @PREFIX_products_stock_history (product, change, date, item, event) SELECT * FROM @PREFIX_products_stock_history_old;

DROP TABLE @PREFIX_products_stock_history_old;
