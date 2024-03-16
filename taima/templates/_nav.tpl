<nav class="tabs">
	{if $current === 'all'}
		<aside>
			{exportmenu}
			{linkbutton target="_dialog" href="edit.php?id_user=%d"|args:$filters.user_id label="Ajouter une tâche" shape="plus"}
		</aside>
	{elseif $current === 'index'}
		<aside>
			{linkbutton shape="plus" label="Nouvelle tâche" href="edit.php?date=%s"|args:$day_date target="_dialog"}
		</aside>
	{/if}

	<ul>
	{if $logged_user.id}
		<li{if $current === 'index'} class="current"{/if}><a href="./">Ma semaine</a></li>
		<li{if $current === 'year'} class="current"{/if}><a href="year.php">Mon résumé</a></li>
	{/if}
{if $session->canAccess($session::SECTION_USERS, $session::ACCESS_ADMIN)}
		<li{if $current === 'all'} class="current"{/if}><a href="all.php">Suivi</a></li>
		<li{if $current === 'stats'} class="current"{/if}><a href="stats.php">Statistiques</a></li>
{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)}
		<li{if $current === 'report'} class="current"{/if}><a href="report.php">Valoriser</a></li>
{/if}
		<li{if $current === 'config'} class="current"{/if}><a href="config.php">Configuration</a></li>
{/if}
	</ul>

	{if $logged_user.id && isset($filters) && !$filters.user_id && !$filters.task_id}
	<ul class="sub">
		<li {if !$filters.except}class="current"{/if}>{link href=$self_url_no_qs label="Tous les membres"}</li>
		<li {if $filters.except}class="current"{/if}>{link href="?except_me" label="Sauf moi-même"}</li>
	</ul>
	{elseif isset($subtitle)}
	<ul class="sub">
		<li class="title">{$subtitle}</li>
	</ul>
	{/if}
</nav>