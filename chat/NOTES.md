## Fonctionnalités actuelles

* Remplace Microsoft Teams, Slack, Discord, Zulip, Mattermost ou Rocket Chat
* Discussions en temps réel
* Création et suppression de salons
* Salons publics, accessibles aux visiteurs
* Salons privés, réservés aux membres de l'association
* Salons privés, sur invitation à des membres ou des intervenants externes
* Messages privés entre deux personnes
* Ajout de réactions / emojis aux messages (+ réactions rapides)
* Communication entre tabs/fenêtres du navigateur pour s'assurer qu'une seule tab peut recevoir les mises à jour en temps réel ([cf. cette discussion](https://linuxfr.org/news/communiquer-avec-le-serveur-depuis-un-navigateur-web-xhr-sse-et-websockets#toc-les-server-sent-events-%C3%A0-la-rescousse))
* Rendu Markdown de base (listes, gras, italique, barré, code, citation), comme [Slack](https://www.markdownguide.org/tools/slack/)
* Transformation automatique des URLs en liens
* Messages sur plusieurs lignes avec Ctrl+Entrée, ou Shift+Entrée
* Envoi de fichier
* Envoi et lecture de messages audio, au format Opus (léger, environ 7 Mo par heure de discussion, soit le poids de 2 photos !)
* Ouverture de visioconférence directement depuis un salon (en utilisant Jitsi)
* Très faible empreinte écologique
* Suppression de message
* Messages éphémères : suppression automatique des messages après un certain délai
* Limitation du nombre de messages enregistrés dans le salon
* Lien permanent vers un message de la discussion
* Suppression des fichiers lors de la suppression du salon

## Fonctionnalités prévues

* mise à jour de la liste des salons (quand un nouveau salon est créé, que quelqu'un vient vous parler en privé, etc.)
* Pouvoir configurer le serveur Jitsi utilisé
* Possibilité de définir une URL de visio différente pour chaque salon (par exemple pour aller sur BBB)
* Transcription automatique des enregistrements audio, via [faster-whisper](https://github.com/SYSTRAN/faster-whisper)
* Possibilité de remonter dans l'historique
* Édition de message
* Archivage de salon : plus personne ne peut rejoindre le salon
* Recherche de message dans les salons
* /me messages
* @Mentions
* Affichage du nombre de messages non lus dans chaque canal
* Affichage de la ligne montrant le dernier message non lu quand on ouvre un salon
* Indicateur du nombre de messages non lus dans le titre de l'onglet
* Envoi de notification par e-mail en cas de mention
* Envoi des discussions par e-mail après X jours d'absence (résumé)
* Possibilité de répondre à un message (fil de discussion)
* API simple pour qu'un bot puisse poster des messages
* Bridge IRC
* Pouvoir choisir son avatar
* Ajout bouton "ouvrir une discussion privée par chat" sur la fiche d'un membre
* Pouvoir exclure quelqu'un d'une discussion
* Pouvoir muter quelqu'un
* Pouvoir restreindre un canal à une catégorie de membres
* Pouvoir masquer la liste des participants d'une discussion
* Masquer par défaut la liste des participants pour un utilisateur anonyme

## Ce qui n'est pas prévu

* Décentralisation
* Bridge Matrix
* Bridge XMPP
* Chiffrement des messages

## How it works

* Messages are sent via HTTP
* Messages are received using SSE (Server Sent Events)

## Online status of users

* When the user connects to the SSE socket, an entry is created into the `plugin_chat_users` table if it doesn't exist
* When the user disconnects from the SSE socket, `last_disconnect` datetime is set, user is considered "unavailable"
* After 15 seconds the user is considered offline
* After X minutes anonymous users are deleted from the table