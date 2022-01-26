INSERT INTO @PREFIX_categories VALUES (1, "Adhésion", NULL);
INSERT INTO @PREFIX_products VALUES (NULL, 1, "Adhésion normale", NULL, 1500, 1, NULL, NULL);
INSERT INTO @PREFIX_products VALUES (NULL, 1, "Adhésion réduite", NULL, 1000, 1, NULL, NULL);

INSERT INTO @PREFIX_categories VALUES (4, "Pièces de vélo d'occasion", NULL);
INSERT INTO @PREFIX_products (category, name, price) VALUES
	(4, "Dérailleur (avant, arrière)", 300),
	(4, "Panier", 500);

INSERT INTO @PREFIX_methods VALUES (1, 'Espèces', 1, NULL, NULL, '530', 1);
INSERT INTO @PREFIX_methods VALUES (2, 'Chèque', 0, NULL, NULL, '5112', 1);

-- Ajout espèces/chèque
INSERT INTO @PREFIX_products_methods SELECT id, 1 FROM @PREFIX_products;
INSERT INTO @PREFIX_products_methods SELECT id, 2 FROM @PREFIX_products;

UPDATE @PREFIX_products SET stock = 0 WHERE category IN (3, 4, 5);