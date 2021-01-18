INSERT INTO @PREFIX_categories VALUES (1, "Adhésion", NULL);
INSERT INTO @PREFIX_products VALUES (NULL, 1, "Adhésion normale", NULL, 1500, 1, NULL, NULL);
INSERT INTO @PREFIX_products VALUES (NULL, 1, "Adhésion réduite", NULL, 1000, 1, NULL, NULL);
INSERT INTO @PREFIX_products VALUES (NULL, 1, "Adhésion soutien", NULL, 2000, 1, NULL, NULL);

INSERT INTO @PREFIX_categories VALUES (2, "Forfaits coup de pouce vélo", NULL);
--INSERT INTO @PREFIX_products VALUES (4, 2, "Forfait révision vélo adhérent", NULL, 1500, 1, NULL, NULL);
INSERT INTO @PREFIX_products VALUES (NULL, 2, "Forfait réparation vélo occasion", 'Réglage d''étrier de frein ou patin
Lubrification de la câblerie frein
Nettoyage de la transmission
Lubrification de la câblerie dérailleur
Réglage d''un dérailleur et de son sélecteur de vitesse
Serrage contrôlé des plateaux
Serrage contrôlé du boîtier de pédalier
Remise en état de roue
Réglage du jeu de direction
Réglage de l''alignement du guidon', 3500, 1, NULL, NULL);

INSERT INTO @PREFIX_categories VALUES (3, "Pièces neuves", NULL);
INSERT INTO @PREFIX_products (category, name, price) VALUES
	(3, "Ampoule pour phare dynamo", 100),
	(3, "Antivol boa ou chaîne", 2000),
	(3, "Antivol U à clé ou à code", 2500),
	(3, "Câble de frein ou de dérailleur", 100),
	(3, "Capuchon de dynamo", 100),
	(3, "Chaîne 1-3v", 600),
	(3, "Chaîne 4-7v", 800),
	(3, "Chaîne 8-9v", 1000),
	(3, "Chambre à air", 500),
	(3, "Fond de jante (par roue)", 100),
	(3, "Gaine de frein (par câble)", 100),
	(3, "Gaine de dérailleur (par câble)", 200),
	(3, "Guidoline tissu noir", 300),
	(3, "Kit rustines", 300),
	(3, "Patin de frein", 200),
	(3, "Phares XLC mini (paire AV+AR)", 500),
	(3, "Phares Reelight", 3000),
	(3, "Pince à pantalon", 100),
	(3, "Pneu mini (550, 600)", 1500),
	(3, "Pneu standard (26"", 700, 28"", 650)", 1000),
	(3, "Mini pompe", 500),
	(3, "Selle", 1000),
	(3, "Sonnette classique (chromée)", 300),
	(3, "Sonnette basique (à languette, plastique noir)", 200),
	(3, "Tendeur", 300),
	(3, "Autre pièce neuve", 1000);

INSERT INTO @PREFIX_categories VALUES (4, "Pièces d'occasion", NULL);
INSERT INTO @PREFIX_products (category, name, price) VALUES
	(4, "Adaptateur (tige de selle, potence)", 100),
	(4, "Ampoule pour phare dynamo", 50),
	(4, "Attache (rapide, selle, roue, panier, siège)", 100),
	(4, "Axe (pédalier, roue)", 100),
	(4, "Béquille", 500),
	(4, "Boîtier de pédalier (ou cartouche)", 400),
	(4, "Butée réglable de gaine (frein, dérailleur)", 50),
	(4, "Câble (frein, dérailleur)", 50),
	(4, "Cadre nu", 1500),
	(4, "Cage à billes", 50),
	(4, "Cale-pied", 200),
	(4, "Carter de chaîne", 300),
	(4, "Cassette (seule)", 100),
	(4, "Chaîne", 300),
	(4, "Chambre à air", 100),
	(4, "Chariot de selle", 100),
	(4, "Clavette", 100),
	(4, "Corne de guidon", 100),
	(4, "Couvre-selle", 200),
	(4, "Cuvette de pédalier", 100),
	(4, "Dérailleur (avant, arrière)", 300),
	(4, "Dynamo", 200),
	(4, "Écarteur de danger", 200),
	(4, "Écrou d'axe de roue", 50),
	(4, "Étrier de frein (avec patins)", 500),
	(4, "Fond de jante (par roue)", 50),
	(4, "Fourche", 500),
	(4, "Gaine (frein, dérailleur, par câble)", 50),
	(4, "Garde-boue", 200),
	(4, "Guidon (nu)", 300),
	(4, "Jante (nue)", 400),
	(4, "Levier de frein", 200),
	(4, "Manette de dérailleur", 200),
	(4, "Manivelle gauche", 100),
	(4, "Manivelle droite", 300),
	(4, "Moyeu", 400),
	(4, "Panier", 500),
	(4, "Patin de frein", 100),
	(4, "Pédale (par pédale)", 100),
	(4, "Pédalier (sans pédales)", 400),
	(4, "Phare (sans ampoule)", 300),
	(4, "Pignon (cassette, roue libre)", 50),
	(4, "Plateau pour pédalier", 100),
	(4, "Pneu", 500),
	(4, "Poignée de guidon", 100),
	(4, "Pompe", 200),
	(4, "Porte-bagage", 500),
	(4, "Porte-bidon", 50),
	(4, "Potence", 300),
	(4, "Rayon (unité)", 50),
	(4, "Rayons (botte)", 500),
	(4, "Roue (nue, avec écrou ou attache)", 1000),
	(4, "Roue libre", 300),
	(4, "Roulettes vélo enfant (paire)", 200),
	(4, "Sacoche", 500),
	(4, "Selle", 500),
	(4, "Siège enfant", 1000),
	(4, "Tige de selle", 200),
	(4, "Autre pièce d'occasion", 100);

INSERT INTO @PREFIX_categories VALUES (5, "Vélo d'occasion", NULL);
INSERT INTO @PREFIX_products VALUES (NULL, 5, "Vélo d'occasion", NULL, 6000, 1, NULL, NULL);

INSERT INTO @PREFIX_methods VALUES (1, 'Espèces', 1, NULL, NULL, NULL);
INSERT INTO @PREFIX_methods VALUES (2, 'Chèque', 0, NULL, NULL, NULL);
INSERT INTO @PREFIX_methods VALUES (3, 'Coup de pouce vélo', 0, 1500, 5000, NULL);

-- Ajout espèces/chèque
INSERT INTO @PREFIX_products_methods SELECT id, 1 FROM @PREFIX_products;
INSERT INTO @PREFIX_products_methods SELECT id, 2 FROM @PREFIX_products;

-- Coup de pouce vélo pièces détachées
INSERT INTO @PREFIX_products_methods SELECT id, 3 FROM @PREFIX_products WHERE category IN (3, 4) AND (
	(name LIKE 'Chaîne%')
	OR (name LIKE 'Chambre à air%')
	OR (name LIKE 'Pneu%')
	OR (name LIKE 'Selle%')
	OR (name LIKE 'Patin%')
	OR (name LIKE 'Gaine%')
	OR (name LIKE 'Câble%')
	OR (name LIKE 'Pédale%')
	OR (name LIKE 'Roue%')
	OR (name LIKE 'Manivelle%')
	OR (name LIKE 'Pédalier%')
	OR (name LIKE 'Manette%')
	OR (name LIKE 'Levier%')
	OR (name LIKE 'Guidon%')
	OR (name LIKE 'Fourche%')
	OR (name LIKE 'Cuvette%')
	OR (name LIKE 'Cassette%')
	OR (name LIKE 'Axe%')
	OR (name LIKE 'Attache%')
	OR (name LIKE 'Boîtier%')
	OR (name LIKE 'Butée%')
	OR (name LIKE 'Chariot%')
	OR (name LIKE 'Clavette%')
	OR (name LIKE 'Écrou%')
	OR (name LIKE 'Dérailleur%')
	OR (name LIKE 'Rayon%')
	OR (name LIKE 'Pignon%')
	OR (name LIKE 'Plateau%')
	OR (name LIKE 'Fond de jante%')
	OR (name LIKE 'Sonnette%')
	OR (name LIKE 'Dynamo%')
	OR (name LIKE 'Ampoule%')
	OR (name LIKE '%reelight%')
	OR (name IN ('Potence', 'Poignée de guidon', 'Tige de selle', 'Phare (sans ampoule)'))
);

INSERT INTO @PREFIX_products_methods SELECT id, 3 FROM @PREFIX_products WHERE category IN (1, 2);