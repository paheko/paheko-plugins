INSERT INTO @PREFIX_categories (id, name) VALUES (1, "Adhésion");
INSERT INTO @PREFIX_products (category, name, price) VALUES (1, "Adhésion normale", 1500);
INSERT INTO @PREFIX_products (category, name, price) VALUES (1, "Adhésion réduite", 1000);

INSERT INTO @PREFIX_categories (id, name) VALUES (4, "Pièces de vélo d'occasion");
INSERT INTO @PREFIX_products (category, name, price) VALUES
	(4, "Dérailleur (avant, arrière)", 300),
	(4, "Panier", 500);

INSERT INTO @PREFIX_methods (id, name, is_cash, account) VALUES (1, 'Espèces', 1, '530');
INSERT INTO @PREFIX_methods (id, name, is_cash, account) VALUES (2, 'Chèque', 0, '5112');

-- Ajout espèces/chèque
INSERT INTO @PREFIX_products_methods (product, method) SELECT id, 1 FROM @PREFIX_products;
INSERT INTO @PREFIX_products_methods (product, method) SELECT id, 2 FROM @PREFIX_products;

UPDATE @PREFIX_products SET stock = 0 WHERE category IN (3, 4, 5);
