<nav class="tabs">
	<ul>
		<li{if $current == 'index'} class="current"{/if}><a href="./">Ma semaine</a></li>
		<li{if $current == 'year'} class="current"{/if}><a href="year.php">Mon résumé</a></li>
{if $session->canAccess($session::SECTION_USERS, $session::ACCESS_ADMIN)}
		<li{if $current == 'add'} class="current"{/if}><a href="others.php">Autres membres</a></li>
		<li{if $current == 'stats'} class="current"{/if}><a href="stats.php">Statistiques</a></li>
		<li{if $current == 'config'} class="current"{/if}><a href="config.php">Configuration</a></li>
{/if}
	</ul>
</nav>