<nav class="tabs">
	{if !empty($show_reset_button)}
	<aside>
		{linkbutton href="?reload" label="Recharger la liste" shape="reset"}
	</aside>
	{/if}
	<ul>
		<li{if $current == 'home'} class="current"{/if}><a href="./">Paiements HelloAsso</a></li>
		{*<li{if $current == 'targets'} class="current"{/if}><a href="targets.php">Synchronisation</a></li>*}
		{if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)}
			<li{if $current == 'config'} class="current"{/if}><a href="config.php">Configuration</a></li>
			<li{if $current == 'config_client'} class="current"{/if}><a href="config_client.php">Connexion à HelloAsso</a></li>
		{/if}
	</ul>
</nav>


{if !empty($restricted)}
	<p class="alert block">Cette version est limitée, et ne pourra importer que les 5 premiers résultats depuis HelloAsso.<br />Merci de réaliser une contribution à Garradin pour débloquer l'extension et participer au financement du projet :)</p>
{/if}
