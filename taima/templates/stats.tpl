{include file="admin/_head.tpl" title="Suivi du temps" plugin_css=['style.css']}

{include file="%s/templates/_nav.tpl"|args:$plugin_root current="stats"}

<nav class="tabs">
	<ul class="sub">
		<li{if $grouping == 'week'} class="current"{/if}><a href="?g=week{if $per_user}&amp;per_user{/if}">Par semaine</a></li>
		<li{if $grouping == 'month'} class="current"{/if}><a href="?g=month{if $per_user}&amp;per_user{/if}">Par mois</a></li>
		<li{if $grouping == 'year'} class="current"{/if}><a href="?g=year{if $per_user}&amp;per_user{/if}">Par année</a></li>
	</ul>
	<ul class="sub">
		<li{if !$per_user} class="current"{/if}><a href="?g={$grouping}">Par tâche</a></li>
		<li{if $per_user} class="current"{/if}><a href="?g={$grouping}&amp;per_user">Par personne</a></li>
	</ul>
</nav>

<table class="list auto">
	{foreach from=$per_week item="week"}
	<tbody>
		<tr>
			<th colspan="3">
				<h2 class="ruler">
				{if $grouping == 'week'}{$week.year} — S{$week.week}
				{elseif $grouping == 'month'}{$week.date|strftime:'%B %Y'}
				{else}{$week.year}{/if}
				</h2>
			</th>
		</tr>
		{foreach from=$week.entries item="entry"}
		<tr>
			<th>{if $per_user && $entry.user_name}<a href="others.php?id_user={$entry.user_id}">{$entry.user_name}</a>{else}{$entry.task_label}{/if}</th>
			<td class="num">{$entry.duration|taima_minutes}</td>
		</tr>
		{/foreach}
	</tbody>
	{/foreach}
</table>


{include file="admin/_foot.tpl"}