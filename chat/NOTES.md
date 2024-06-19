## Fonctionnalités actuelles

* Discussions en temps réel
* Création et suppression de salons
* Salons publics, accessibles aux visiteurs
* Salons privés, réservés aux membres de l'association
* Salons privés, sur invitation à des membres ou des intervenants externes
* Messages privés entre deux personnes
* Ajout de réactions / emojis aux messages
* Envoi et lecture de messages audio, au format Opus (léger, environ 7 Mo par heure de discussion, soit le poids de 2 photos !)
* Communication entre tabs/fenêtres du navigateur pour s'assurer qu'une seule tab peut recevoir les mises à jour en temps réel ([cf. cette discussion](https://linuxfr.org/news/communiquer-avec-le-serveur-depuis-un-navigateur-web-xhr-sse-et-websockets#toc-les-server-sent-events-%C3%A0-la-rescousse))
* Rendu Markdown de base (listes, gras, italique, barré, code, citation), comme [Slack](https://www.markdownguide.org/tools/slack/)
* Transformation automatique des URLs en liens
* Messages sur plusieurs lignes avec Ctrl+Entrée, ou Shift+Entrée
* Ouverture de visioconférence directement depuis un salon (en utilisant Jitsi)
* Très faible empreinte écologique

## Fonctionnalités prévues

* Suppression de message
* Édition de message
* Envoi de fichier
* Archivage de salon : plus personne ne peut rejoindre le salon
* Suppression des fichiers lors de la suppression du salon
* Recherche de message dans les salons
* /me messages
* @Mentions
* Browser notifications
* Email notifications of mentions
* Threads

## How it works

* Messages are sent via HTTP
* Messages are received using SSE (Server Sent Events)

## Online status of users

* When the user connects to the SSE socket, an entry is created into the `plugin_chat_users` table if it doesn't exist
* When the user disconnects from the SSE socket, `last_disconnect` datetime is set, user is considered "unavailable"
* After 15 seconds the user is considered offline
* After X minutes anonymous users are deleted from the table