{if $session->canAccess($session::SECTION_USERS, $session::ACCESS_WRITE)}
<nav class="tabs">
	<ul>
		<li{if $current == 'bookings'} class="current"{/if}><a href="{plugin_url file="bookings.php"}">Voir les inscrits</a></li>
		{if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)}
			<li{if $current == 'config'} class="current"{/if}><a href="{plugin_url file="config.php"}">Configuration</a></li>
		{/if}
	</ul>
</nav>
{/if}