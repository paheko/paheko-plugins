{include file="admin/_head.tpl" title="Suivi du temps" plugin_css=['style.css']}

{if $session->canAccess($session::SECTION_USERS, $session::ACCESS_ADMIN)}
<nav class="tabs">
	<ul>
		<li><a href="./">Mon temps</a></li>
		<li class="current"><a href="stats.php">Statistiques</a></li>
		<li><a href="config.php">Configuration</a></li>
	</ul>
	<ul class="sub">
		<li{if $grouping == 'week'} class="current"{/if}><a href="?g=week">Par semaine</a></li>
		<li{if $grouping == 'month'} class="current"{/if}><a href="?g=month">Par mois</a></li>
		<li{if $grouping == 'year'} class="current"{/if}><a href="?g=year">Par année</a></li>
	</ul>
	<ul class="sub">
		<li{if !$per_user} class="current"{/if}><a href="?g={$grouping}">Par tâche</a></li>
		<li{if $per_user} class="current"{/if}><a href="?g={$grouping}&per_user">Par personne</a></li>
	</ul>
</nav>
{/if}

<table class="">
	{foreach from=$per_week item="week"}
	<tbody>
		<tr>
			<th colspan="3"><h2 class="ruler">
				{if $grouping == 'week'}{$week.year} — S{$week.week}
				{elseif $grouping == 'month'}{$week.date|strftime:'%B %Y'}
				{else}{$week.year}{/if}
			</h2></th>
		</tr>
		{foreach from=$week.entries item="entry"}
		<tr>
			<th>{if $per_user}{$entry.user_name}{else}{$entry.task_label}{/if}</th>
			<td>{$entry.duration|taima_minutes}</td>
			<td></td>
		</tr>
		{/foreach}
	</tbody>
	{/foreach}
</table>


{include file="admin/_foot.tpl"}