INSERT INTO @PREFIX_categories VALUES (1, "Adhésion", NULL);
INSERT INTO @PREFIX_products VALUES (NULL, 1, "Adhésion normale", NULL, 1500, 1, NULL, NULL);
INSERT INTO @PREFIX_products VALUES (NULL, 1, "Adhésion réduite", NULL, 1000, 1, NULL, NULL);

INSERT INTO @PREFIX_categories VALUES (3, "Pièces neuves", NULL);
INSERT INTO @PREFIX_products (category, name, price) VALUES
	(3, "Ampoule pour phare dynamo", 100),
	(3, "Chambre à air", 500),
	(3, "Pneu standard (26"", 700, 28"", 650)", 1000),
	(3, "Selle", 1000),
	(3, "Sonnette basique (à languette, plastique noir)", 200),
	(3, "Autre pièce neuve", 1000);

INSERT INTO @PREFIX_categories VALUES (4, "Pièces d'occasion", NULL);
INSERT INTO @PREFIX_products (category, name, price) VALUES
	(4, "Axe (pédalier, roue)", 100),
	(4, "Dérailleur (avant, arrière)", 300),
	(4, "Moyeu", 400),
	(4, "Panier", 500),
	(4, "Autre pièce d'occasion", 100);

INSERT INTO @PREFIX_categories VALUES (5, "Vélo d'occasion", NULL);
INSERT INTO @PREFIX_products VALUES (NULL, 5, "Vélo d'occasion", NULL, 6000, 1, NULL, NULL);

INSERT INTO @PREFIX_methods VALUES (1, 'Espèces', 1, NULL, NULL, NULL, 1);
INSERT INTO @PREFIX_methods VALUES (2, 'Chèque', 0, NULL, NULL, NULL, 1);

-- Ajout espèces/chèque
INSERT INTO @PREFIX_products_methods SELECT id, 1 FROM @PREFIX_products;
INSERT INTO @PREFIX_products_methods SELECT id, 2 FROM @PREFIX_products;
