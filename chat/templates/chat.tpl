{include file="_head.tpl" title=$channel.name current="plugin_%s"|args:$plugin.id hide_title=true}

{form_errors}

<div id="chat" data-channel-id="{$channel.id}">
	<nav class="channels">
		{if $session->canAccess($session::SECTION_USERS, $session::ACCESS_ADMIN)}
		<aside>
			{linkbutton shape="plus" label="Nouvelle discussion" href="!p/chat/edit.php?id=%d"|args:$channel.id target="_dialog"}
		</aside>
		{/if}
		<ul>
		{foreach from=$channels item="c"}
			<li {if $c.id === $channel.id}class="current"{/if}>{link href="?id=%d"|args:$c.id label=$c.name}</li>
		{/foreach}
		</ul>
	</nav>

	<section class="channel">
		<h2>
		{if $channel.access === $channel::ACCESS_DIRECT}
			{chat_avatar object=$recipient name=true online=true}
		{else}
			{$channel.name}
		{/if}
		</h2>
		<aside>
			{if $recipient.id_user}
				{linkbutton href="!users/details.php?id=%d"|args:$recipient.id_user label="Fiche membre" shape="user"}
			{elseif $channel.access !== $channel::ACCESS_DIRECT && $session->canAccess($session::SECTION_USERS, $session::ACCESS_ADMIN)}
				{linkbutton shape="users" label="Participant⋅e⋅s" href="%s/users.php?id=%d"|args:$plugin_url:$channel.id target="_dialog"}
				{linkbutton shape="edit" label="Gérer" href="!p/chat/edit.php?id=%d"|args:$channel.id target="_dialog"}
			{/if}
				{linkbutton href="search.php?id=%d"|args:$channel.id shape="search" title="Rechercher dans cette discussion" target="_dialog" label=""}
				{linkbutton href="#" shape="camera" title="Lancer une réunion vidéo" target="_dialog" label=""}
		</aside>
		<article>{$channel.description|markdown|raw}</article>
		<h5>{$channel->getAccessLabel()}</h5>
	</section>

	<section class="messages">
		<div>
		<?php $current_user = null; $current_day = null; ?>
		{foreach from=$messages item="message"}
			{$message|chat_message_html:$me:$current_day:$current_user|raw}
		{/foreach}
	</div>
	</section>

	<section class="chatbox">
		<form method="post" action="" data-disable-progress="1">
			{csrf_field key=$csrf_key}
			<article class="text">
				<header>
					{button title="Joindre un fichier" shape="attach"}
					{button title="Enregistrer un extrait audio" shape="microphone" id="record-button"}
				</header>
				{input type="textarea" cols=50 rows=2 name="message"}
			</article>
			<article class="audio">
				<header>
					{button title="Annuler" shape="delete" id="record-cancel-button"}
				</header>
				<div id="recorder-container"></div>
				<div class="recording">
					<h3>Enregistrement en cours…</h3>
					{button label="Arrêter l'enregistrement" id="record-stop-button" class="stop"}
				</div>
			</article>
			<article class="file">
			</article>
			<footer>
				{button type="submit" title="Envoyer" shape="right"}
				<input type="hidden" name="send" value="1" />
			</footer>
		</form>
	</section>
</div>

<script type="text/javascript" src="{$plugin_url}chat.js">
</script>

{include file="_foot.tpl"}
