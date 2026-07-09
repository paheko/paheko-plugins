* Test PDF weasyprint (HS)
* Test PDF chromium + gs (HS)

* Ajout confirmation à l'annulation de facture
* Empêcher la suppression d'un avoir lié à une facture (sinon on pourrait dé-annuler une facture)
* Pouvoir re-créer une facture à partir d'un devis si la facture a été supprimée
* Afficher un lien vers la facture depuis le devis quand une facture a été créée
* Afficher un lien vers la facture depuis l'avoir
* Afficher un lien vers l'avoir depuis la facture
* Pouvoir annuler une facture payée
* Support des remises au niveau de la facture (pas par ligne)
* Création d'écritures depuis les factures (facture, paiements, remboursements)

# Clients

* Pouvoir créer un client depuis le sélecteur
* Pouvoir supprimer un client

# Config

* Pouvoir choisir le numéro de la première facture et du premier devis
* Pouvoir indiquer le numéro de TVA
* Champ permettant de choisir l'exemption de TVA par défaut

# Dans Paheko core

Configuration :

- Ajouter les champs suivants : "code postal", "ville" (pré-remplir avec l'adresse si possible)

# Plus tard

* Pouvoir voir toutes les factures d'un client
* Pouvoir associer un code de compte à un client (s'il n'existe pas dans le plan comptable cible, le créer)
* Pouvoir créer un avoir manuellement (dans ce cas il faudra indiquer numéro de l'ancienne facture + date d'émission, obligatoire pour Factur-X)
