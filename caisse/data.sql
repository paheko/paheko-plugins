INSERT INTO @PREFIX_categories (id, name, account) VALUES (1, "Adhésion", '756');
INSERT INTO @PREFIX_products (category, name, price) VALUES (1, "Adhésion normale", 1500);
INSERT INTO @PREFIX_products (category, name, price) VALUES (1, "Adhésion réduite", 1000);

INSERT INTO @PREFIX_categories (id, name, account) VALUES (4, 'Pièces de vélo neuves (exemple)', '707');
INSERT INTO @PREFIX_products (category, name, price) VALUES
	(4, "Dérailleur (avant, arrière)", 1200),
	(4, "Panier", 1500);

INSERT INTO @PREFIX_methods (id, name, type, account, position) VALUES (1, 'Espèces', 1, '530', 1);
INSERT INTO @PREFIX_methods (id, name, type, account, position) VALUES (2, 'Chèque', 0, '5112', 2);
INSERT INTO @PREFIX_methods (id, name, type, account, position) VALUES (3, 'Ardoise', 2, '4110', 3);

-- Ajout espèces/chèque/dette
INSERT INTO @PREFIX_products_methods (product, method) SELECT id, 1 FROM @PREFIX_products;
INSERT INTO @PREFIX_products_methods (product, method) SELECT id, 2 FROM @PREFIX_products;
INSERT INTO @PREFIX_products_methods (product, method) SELECT id, 3 FROM @PREFIX_products;

UPDATE @PREFIX_products SET stock = 0 WHERE category IN (3, 4, 5);
