# Discussions

Cette extension permet de créer et gérer des discussions, qui peuvent être suivies via le site web (comme un forum) ou par e-mail (comme une liste de discussion).

Elle répond aux besoins à la fois des utilisateurs lambdas via son interface web et à la fois des utilisateurs avancés, via la gestion par e-mail.

Elle permet également de gérer une adresse e-mail partagée, par exemple `contact@` ou `support@`, y suivre les discussions et coordonner les réponses entre modos. Très utile pour gérer les messages envoyés à l'adresse publique d'une association !

Enfin, il est également possible de configurer un forum pour que les messages envoyés par e-mail soient chiffrés avec PGP, permettant d'avoir une liste de discussion confidentielle, comme le propose [Schleuder](https://schleuder.org/schleuder/docs/concept.html) par exemple.

Cette extension peut utilement remplacer les outils suivants :

- forum web (Discourse, PhpBB, FluxBB, Zulip…)
- liste de discussion (Sympa, Framaliste, Mailman…)
- liste de discussion sécurisée avec PGP ([Schleuder](https://schleuder.org/schleuder/docs/concept.html))
- adresse e-mail partagée type contact ou support ([FreeScout](https://freescout.net), HelpScout, Missive, Hiver…)

Cette extension n'est pas conçue pour faire une liste de diffusion, car chaque abonné⋅e doit confirmer son adresse e-mail avant de pouvoir recevoir des messages.

## Fonctionnalités

* Interface web type forum
* Réception et envoi de messages par e-mail, type liste de discussion
* Les abonné⋅e⋅s peuvent être des membres de Paheko, ou des personnes externes (inscription par adresse e-mail)
* Blocage optionnel des fichiers joints qui ne sont pas sûrs
* Suppression (optionnelle) des fichiers joints trop lourds
* Redimensionnement (optionnel) des images jointes
* Ré-écriture des adresses des expéditeurs (pour éviter les problèmes d'émetteur avec DMARC/SPF)

## Roadmap

* Messages internes, envoyés seulement entre modérateurs (pour discussion sur un message d'adresse partagée)
* Création de "fiche client" pour les messages sur une adresse partagée
* Positionner Reply-To sur la liste + expéditeur original si celui-ci n'est pas abonné à la liste, sinon Reply-To = liste
* Utilisation de POP3/IMAP/SMTP pour une adresse email externe
* Vérification des messages entrant avec PGP
* Chiffrement + signature des messages sortants avec PGP
* Joindre la clé publique de la liste avec le message de bienvenue
* Chiffrer le stockage de la clé privée PGP (nécessite la gestion du chiffrement et des vaults dans Paheko)
* Vérification SPF du nom de domaine de l'émetteur
* Vérification DKIM du message reçu

## Historique

Cette extension est issue du travail effectué depuis plus 2010 Narragoon, un ancien panel de gestion de site web inspiré par AlternC.

# Interface avec le serveur mail

## Configuration avancée

`DISCUSS_DOMAINS`: liste de noms de domaines disponibles pour créer une nouvelle liste
`DISCUSS_SEPARATOR` : séparateur de commande dans l'adresse email (défaut : `+`)

## Signaux

* `discuss.address.verify` : vérifier qu'une adresse est disponible
* `discuss.address.create` : créer une adresse
* `discuss.address.delete` : supprimer une adresse

En cas de modification de l'adresse, les signaux `delete` et `create` sont appelés.

## Webhook pour la réception de message

Il est possible de configurer votre serveur mail pour appeler un webhook quand un message est reçu sur une adresse e-mail.

### Exemple : Exim

Router:

```
webhook_router:
  driver = accept
  domains = example.org
  local_parts = myaddress : otherlist
  transport = webhook_transport
```

Transport:

```
webhook_transport:
  driver = pipe
  command = /home/mail/webhook.php
  message_prefix =
  return_fail_output
  log_fail_output
  log_defer_output
  temp_errors = 75
  timeout = 60s
```

Script dans `/home/mail/webhook.php`:

```
