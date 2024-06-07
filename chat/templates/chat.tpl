{include file="_head.tpl" title=$channel.name current="plugin_%s"|args:$plugin.id hide_title=true}

{form_errors}

<div id="chat">
	<section class="channels">
	</section>

	<section class="users">
		<table>
			{foreach from=$users item="user"}
			<tr>
				<th>{$user.name}</th>
			</tr>
			{/foreach}
		</table>
	</section>

	<section class="messages">
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
						<figure><img src="/user/avatar/{if $message.id_user}{$message.id_user}{else}chat_{$message.user_name|md5}{/if}" /></figure>
						<strong>{$message.user_name}</strong>
						<time>{$message.added|date:'H:i'}</time>
					</header>
				{/if}
				<div class="web-content">{$message.content}</div>
			</article>
		{/foreach}
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
