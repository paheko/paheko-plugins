<nav class="tabs">
	<aside>
	{if $current === 'config'}
		{linkbutton href="import.php" shape="import" label="Import de tâches"}
	{/if}
	{if $session->canAccess($session::SECTION_USERS, $session::ACCESS_WRITE)}
		{linkbutton shape="plus" label="Saisie pour un autre membre" href="edit.php"}
	{/if}
	</aside>
	<ul>
	{if $logged_user.id}
		<li{if $current === 'index'} class="current"{/if}><a href="./">Ma semaine</a></li>
		<li{if $current === 'year'} class="current"{/if}><a href="year.php">Mon résumé</a></li>
	{/if}
		<li{if $current === 'all'} class="current"{/if}><a href="all.php">Suivi</a></li>
{if $session->canAccess($session::SECTION_USERS, $session::ACCESS_WRITE)}
		<li{if $current === 'stats'} class="current"{/if}><a href="stats.php">Statistiques</a></li>
{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)}
		<li{if $current === 'report'} class="current"{/if}><a href="report.php">Valoriser</a></li>
{/if}
		<li{if $current === 'config'} class="current"{/if}><a href="config.php">Configuration</a></li>
{/if}
	</ul>
</nav>