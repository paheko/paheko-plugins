{if $session->canAccess('membres', Membres::DROIT_ECRITURE)}
<nav class="tabs">
	<ul>
		<li{if $current == 'bookings'} class="current"{/if}><a href="{plugin_url file="bookings.php"}">Voir les inscrits</a></li>
		{if $session->canAccess('config', Membres::DROIT_ADMIN)}
			<li{if $current == 'config'} class="current"{/if}><a href="{plugin_url file="config.php"}">Configuration</a></li>
		{/if}
	</ul>
</nav>
{/if}