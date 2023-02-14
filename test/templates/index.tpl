{include file="_head.tpl" title="Extension — %s"|args:$plugin.nom}

{include file="./_nav.tpl" current="index"}

<p class="alert block">Cette extension n'est qu'un test.</p>

{if $plugin.config.display_button}
	<p class="confirm block">Le bouton est affiché sur la page d'accueil</p>
{else}
	<p class="error block">Le bouton est <strong>caché</strong> sur la page d'accueil</p>
{/if}


{include file="_foot.tpl"}
