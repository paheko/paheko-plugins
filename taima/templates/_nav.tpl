<nav class="tabs">
	{if $current == 'others'}
	<aside>
		{linkbutton target="_dialog" href="others_edit.php?id_user=%d"|args:$user.id label="Ajouter une tâche" shape="plus"}
	</aside>
	{/if}

	<ul>
		<li{if $current == 'index'} class="current"{/if}><a href="./">Ma semaine</a></li>
		<li{if $current == 'year'} class="current"{/if}><a href="year.php">Mon résumé</a></li>
{if $session->canAccess($session::SECTION_USERS, $session::ACCESS_ADMIN)}
		<li{if $current == 'others'} class="current"{/if}><a href="others.php">Autres membres</a></li>
		<li{if $current == 'stats'} class="current"{/if}><a href="stats.php">Statistiques</a></li>
{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)}
		<li{if $current == 'report'} class="current"{/if}><a href="report.php">Valoriser</a></li>
{/if}
		<li{if $current == 'config'} class="current"{/if}><a href="config.php">Configuration</a></li>
{/if}
	</ul>
</nav>