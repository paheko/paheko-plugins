* Test PDF weasyprint (HS)
* Test PDF chromium + gs (HS)
* Prévenir avant publication qu'une facture non conforme (SIREN manquant) ne pourra pas être envoyée

# Clients

* Pouvoir créer un client depuis le sélecteur
* Pouvoir supprimer un client

# Config

* Pouvoir choisir le numéro de la première facture et du premier devis
* Pouvoir indiquer le numéro de TVA
* Champ permettant de choisir l'exemption de TVA par défaut

# Dans Paheko core

Configuration :

- Ajouter les champs suivants : "code postal", "ville" (pré-remplir avec l'adresse si possible), "numéro TVA" et "numéro SIRET" (business_number)
- Rajouter une page dans la config "Informations légales", et y mettre ces infos

# Plus tard

* Pouvoir voir toutes les factures d'un client
* Pouvoir associer un code de compte à un client (s'il n'existe pas dans le plan comptable cible, le créer)
