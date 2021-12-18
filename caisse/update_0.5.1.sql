DROP TABLE @PREFIX_stock_events;

CREATE TABLE IF NOT EXISTS @PREFIX_stock_events (
	-- Stock events (eg. delivery from supplier)
	id INTEGER NOT NULL PRIMARY KEY,
	date TEXT NOT NULL,
	type INTEGER NOT NULL,
	label TEXT NOT NULL,
	applied INTEGER NOT NULL
);
