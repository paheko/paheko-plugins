{include file="_head.tpl" title=$channel.name current="plugin_%s"|args:$plugin.id hide_title=true}

{form_errors}

<div id="chat">
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
		{if $channel.access === $channel::ACCESS_PM}
			{chat_avatar object=$recipient name=true online=true}
		{else}
			{$channel.name}
		{/if}
		</h2>
		<aside>
			{if $recipient.id_user}
				{linkbutton href="!users/details.php?id=%d"|args:$recipient.id_user label="Fiche membre" shape="user"}
			{elseif $channel.access !== $channel::ACCESS_PM && $session->canAccess($session::SECTION_USERS, $session::ACCESS_ADMIN)}
				{linkbutton shape="users" label="Participant⋅e⋅s" href="%s/users.php?id=%d"|args:$plugin_url:$channel.id target="_dialog"}
				{linkbutton shape="edit" label="Gérer" href="!p/chat/edit.php?id=%d"|args:$channel.id target="_dialog"}
			{/if}
				{linkbutton href="search.php?id=%d"|args:$channel.id shape="search" title="Rechercher dans cette discussion" target="_dialog" label=""}
		</aside>
		<article>{$channel.description|markdown|raw}</article>
		<h5>{$channel->getAccessLabel()}</h5>
	</section>

	<section class="messages">
		<div>
		<?php $current_user = null; $current_day = null; ?>
		{foreach from=$messages item="message"}
			{assign var="date" value=$message.added|date:'Ymd'}
			{if $current_day !== $date}
				{assign var="current_day" value=$message.added|date:'Ymd'}
				<h4 class="ruler">{$message.added|date_long}</h4>
			{/if}
			<article>
				{if $current_user !== $message.user_name}
					{assign var="current_user" value=$message.user_name}
					<header>
						{chat_avatar href="?id=%d&with=%d"|args:$channel.id:$message.id_user object=$message}
						<strong><a href="?id={$channel.id}&amp;with={$message.id_user}">{$message.user_name}</a></strong>
						<time>{$message.added|date:'H:i'}</time>
					</header>
					<div class="web-content">{$message.content}</div>
				{else}
					<div class="line">
						<time>{$message.added|date:'H:i'}</time>
						<div class="web-content">{$message.content|nl2br|raw}</div>
					</div>
				{/if}
				<footer>
					{if $message.id_user === $me.id}
					{button shape="edit" title="Éditer"}
					{button shape="delete" title="Supprimer"}
					{/if}

					{button shape="chat" title="Répondre"}
					{button shape="smile" title="Réaction"}
				</footer>
			</article>
		{/foreach}
	</div>
	</section>

	<section class="chatbox">
		<form method="post" action="">
			{input type="textarea" cols=50 rows=2 name="text" required=true}
			{csrf_field key=$csrf_key}
			{button type="submit" name="send" title="Envoyer" shape="right"}
		</form>
	</section>
</div>

<script type="text/javascript" src="{$plugin_url}chat.js">
</script>

{include file="_foot.tpl"}
