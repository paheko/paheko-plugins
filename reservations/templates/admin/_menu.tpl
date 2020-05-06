{if $session->canAccess('membres', Membres::DROIT_ADMIN)}
<ul class="actions">
	<li{if $current == 'index'} class="current"{/if}><a href="{plugin_url}">Mes réservations</a></li>
	<li{if $current == 'bookings'} class="current"{/if}><a href="{plugin_url file="bookings.php"}">Voir les inscrits</a></li>
	{if $session->canAccess('config', Membres::DROIT_ADMIN)}
		<li{if $current == 'config'} class="current"{/if}><a href="{plugin_url file="config.php"}">Configuration</a></li>
	{/if}
</ul>
{/if}