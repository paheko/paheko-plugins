DROP TABLE @PREFIX_products_stock_history;

CREATE TABLE IF NOT EXISTS @PREFIX_products_stock_history (
	-- History of stock changes for a product
	product INTEGER NOT NULL REFERENCES @PREFIX_products (id) ON DELETE CASCADE,
	change INTEGER NOT NULL, -- Number of items removed or added to stock: can be negative or positive
	date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Date of change
	item INTEGER NULL REFERENCES @PREFIX_tabs_items (id) ON DELETE CASCADE, -- Link to item in a customer tab
	event INTEGER NULL REFERENCES @PREFIX_stock_events (id) ON DELETE CASCADE -- Link to stock event
);

UPDATE @PREFIX_products SET stock = 0 WHERE category IN (3, 4, 5);

-- Update stock from what we have for tabs items
UPDATE @PREFIX_products SET stock = -(SELECT SUM(ti.qty) FROM @PREFIX_tabs_items ti WHERE ti.product = @PREFIX_products.id)
	WHERE stock IS NOT NULL AND id IN (SELECT DISTINCT ti.product FROM @PREFIX_tabs_items ti WHERE ti.product IS NOT NULL);

INSERT INTO @PREFIX_products_stock_history (product, change, date, item, event)
	SELECT ti.product, -SUM(ti.qty), ti.added, ti.id, NULL
	FROM @PREFIX_tabs_items ti
	INNER JOIN @PREFIX_tabs t ON t.id = ti.tab
	INNER JOIN @PREFIX_sessions s ON s.id = t.session
	INNER JOIN @PREFIX_products p ON p.id = ti.product
	WHERE s.closed IS NOT NULL AND ti.product IS NOT NULL AND p.stock IS NOT NULL
	GROUP BY ti.id, ti.product;

