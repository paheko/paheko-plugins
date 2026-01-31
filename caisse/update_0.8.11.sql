CREATE TABLE IF NOT EXISTS @PREFIX_products_links (
	-- Links between products: add all the linked products to the cart when adding the parent product
	id_product INTEGER NOT NULL REFERENCES @PREFIX_products (id) ON DELETE CASCADE,
	id_linked_product INTEGER NOT NULL REFERENCES @PREFIX_products (id) ON DELETE CASCADE
);

CREATE UNIQUE INDEX IF NOT EXISTS @PREFIX_products_links_unique ON @PREFIX_products_links (id_product, id_linked_product);

ALTER TABLE @PREFIX_tabs_items ADD COLUMN id_parent_item INTEGER NULL REFERENCES @PREFIX_tabs_items (id) ON DELETE CASCADE;
