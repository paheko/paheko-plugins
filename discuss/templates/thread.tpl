{include file="_head.tpl" title="%s - %s"|args:$thread.subject,$list.title}

<nav>
	<a href="./">&larr; Retour à la liste des messages</a>
</nav>

<section class="thread">
	<header>
		<h1>{$thread.subject}</h1>

	{foreach from=$messages item="msg"}
	<article class="msg {if $msg->isFromModerator()}moderator{/if}">
		<header>
			<nav>
				<a id="msg-{$msg.id}" href="#msg-{$msg.id}">Message #{$msg.id}</a>
				{*
				{if $can_reply}
	function reply_button(string $list, array $msg): string
	{
	    $data = sprintf('mailto:%s?Subject=%s&In-Reply-To=%s', $list, rawurlencode($msg['subject']), rawurlencode($msg['message_id']));
	    $data = strrev(str_rot13($data));

	    return sprintf('<a href="#fail" data-d="%s" onclick="return replyTo(this);">Répondre à ce message</a>', htmlspecialchars($data));
	}
				| <a href="#fail" data-d="{$msg->protected_reply_url()}" onclick="return replyTo(this);">Répondre à ce message</a>
				*}
			</nav>

			<h3>{$msg.from_name}</h3>
			<h4>{$msg.date->format('d/m/Y H:i')}</h4>
		</header>
		<pre>{$msg.content|message_format}</pre>
	</article>
	{/foreach}
</header>

{include file="_foot.tpl"}
