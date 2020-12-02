{include file="admin/_head.tpl" title="Extension — %s"|args:$plugin.nom current="plugin_%s"|args:$plugin.id}

<p class="alert block">Cette extension n'est qu'un test.</p>

{if $plugin.config.display_hello}
	<h3>L'affichage du message de bienvenue est activé</h3>
{else}
	<h3>L'affichage du message de bienvenue est désactivé&nbsp;!</h3>
{/if}


{include file="admin/_foot.tpl"}
