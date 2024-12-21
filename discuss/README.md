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

## Roadmap

* Messages internes, envoyés seulement entre modérateurs (pour discussion sur un message d'adresse partagée)
* Création de "fiche client" pour les messages sur une adresse partagée
* Vérification des messages entrant avec PGP
* Chiffrement + signature des messages sortants avec PGP

## Historique

Cette extension est issue du travail effectué depuis plus 2010 Narragoon, un ancien panel de gestion de site web inspiré par AlternC.
