* Test PDF weasyprint (HS)
* Test PDF chromium + gs (HS)

* Empêcher la suppression d'un avoir lié à une facture (sinon on pourrait dé-annuler une facture)
* Pouvoir re-créer une facture à partir d'un devis si la facture a été supprimée
* Afficher un lien vers la facture depuis le devis quand une facture a été créée
* Afficher un lien vers la facture depuis l'avoir
* Afficher un lien vers l'avoir depuis la facture
* Pouvoir annuler une facture payée
* Support des remises au niveau de la facture (pas par ligne)

# Réception de facture

* Afficher le nombre de factures non approuvées à côté du libellé "Reçues" dans la liste des onglets
* Importer les événements SuperPDP à l'import d'une facture
* Pouvoir supprimer une facture importée manuellement
* Pouvoir envoyer l'événement 210 "refusée"
* Pouvoir ajouter manuellement une facture avec ses lignes (libellé, montant)

# Envoi de factures

* Validation des factures générées
* Envoi de facture
* Envoyer l'événement 212 "encaissée" avec le montant à l'enregistrement d'un paiement

# Création d'écriture de facture

* Création d'écritures depuis les factures (facture, paiements, remboursements)
* Enregistrer l'association libellé de la ligne -> numéro de compte du plan comptable, pour pouvoir pré-remplir l'écriture la prochaine fois

# Clients

* Secret professionnel : https://www.compta-online.com/facturation-electronique-et-secret-professionnel-ao8798

* Pouvoir créer un client depuis le sélecteur
* Pouvoir chercher un client
* Rajouter un champ "notes" sur les fiches client

# Plus tard

* Pouvoir voir toutes les factures d'un client
* Pouvoir associer un code de compte à un client (s'il n'existe pas dans le plan comptable cible, le créer)
* Pouvoir créer un avoir manuellement (dans ce cas il faudra indiquer numéro de l'ancienne facture + date d'émission, obligatoire pour Factur-X)

Se faire référencer ici : https://fnfe-mpe.org/factur-x/qui-propose-factur-x/
# Trucs demandés, à surveiller

* Recevoir une copie du mail envoyant la facture / devis
* Pouvoir indiquer le code du service exécutant dans le client
* Pouvoir avoir un "catalogue" de lignes qu'on peut re-ajouter aux nouveaux devis / factures