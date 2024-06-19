## Fonctionnalités actuelles

* Discussions en temps réel
* Envoi et lecture de messages audio au format Opus (léger, environ 7 Mo par heure de discussion, soit le poids de 2 photos !)
* 

## Features

* Real-time chat
* Chatrooms confidentiality:
  * Private: only logged-in users can access, eventually restricted to one category
* Auto-linkify of URLs
* Multi-line messages with Shift+Enter
* Some kind of Markdown rendering (lists, bold, italic, strikethrough, code, quote), just like [Slack support](https://www.markdownguide.org/tools/slack/)

## Later

* Reactions: https://github.com/julien-marcou/unicode-emoji
* Delete messages
* Attach internal files / new files to messages
* Archiving of channels: no one can join, messages are displayed, that's all
* Ability to send invitations to a chatroom to external users
  * Public: available for everyone on the website
  * Invite-only: only invited people can join
* /me messages
* @Mentions
* Browser notifications
* Email notifications of mentions
* Threads
* Audio messages using [Opus](https://github.com/zhukov/opus-recorder)
* Make sure only one tab can use SSE at the same time to avoid inter-tabs lock: https://linuxfr.org/news/communiquer-avec-le-serveur-depuis-un-navigateur-web-xhr-sse-et-websockets#toc-les-server-sent-events-%C3%A0-la-rescousse
* Jitsi chat integration (iframe): <div id="meet"></div>
<script src='https://meet.jit.si/external_api.js'></script>
<script>
	const domain = 'meet.jit.si';
const options = {
    roomName: 'JitsiMeetAPIExample',
    width: 700,
    height: 700,
    parentNode: document.querySelector('#meet'),
    lang: 'de'
};
const api = new JitsiMeetExternalAPI(domain, options);</script>

## How it works

* Messages are sent via HTTP
* Messages are received using SSE (Server Sent Events)

## Online status of users

* When the user connects to the SSE socket, an entry is created into the `plugin_chat_users` table if it doesn't exist
* When the user disconnects from the SSE socket, `last_disconnect` datetime is set, user is considered "unavailable"
* After 15 seconds the user is considered offline
* After X minutes anonymous users are deleted from the table