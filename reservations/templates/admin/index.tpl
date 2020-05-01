{include file="admin/_head.tpl" title=$plugin.nom current="plugin_%s"|args:$plugin.id}

{if $session->canAccess('membres', Membres::DROIT_ADMIN)}
<ul class="actions">
	<li class="current"><a href="{plugin_url}">Mes réservations</a></li>
	<li><a href="{plugin_url file="bookings.php"}">Voir les inscrits</a></li>
	{if $session->canAccess('config', Membres::DROIT_ADMIN)}
		<li><a href="{plugin_url file="config.php"}">Configuration</a></li>
	{/if}
</ul>
{/if}

{include file="%s/templates/_form.tpl"|args:$plugin_root ask_name=false}

{include file="admin/_foot.tpl"}
