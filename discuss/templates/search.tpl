{include file="_head.tpl" title="Recherche - %s"|args:$list.title}

<nav>
	<a href="./">&larr; Retour à la liste des messages</a>

	{if $order == 'date'}
		| <a href="?search={$query|escape:'url'}">Trier par pertinence</a>
	{else}
		| <a href="?search={$query|escape:'url'}&amp;date">Trier par date</a>
	{/if}

</nav>

<section class="thread search">

{foreach from=$messages item="msg"}
	<article class="msg">
		<header>
			<h2><a href="./{$msg.uri}#msg-{$msg.id}">{$msg.subject}</a></h2>
			<h3>{$msg.from_name}</h3>
			<h4>{$msg.date->format('d/m/Y H:i')}</h4>
		</header>
		<pre>{$snippet|message_format:true}</pre>
	</article>
{/foreach}

</section>

{include file="_foot.tpl"}
