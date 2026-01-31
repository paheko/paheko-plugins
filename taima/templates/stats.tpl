{include file="_head.tpl" title="Suivi du temps"}

{include file="./_nav.tpl" current="stats"}

<nav class="tabs">
	<aside>
		{if !$filters_uri}
			{linkbutton shape="search" href="#" id="filterFormButton" label="Filtrer par dates" onclick="var a = $('#filterForm'); a.disabled = false; g.toggle(a, true); this.remove(); var a = $('#compareFormButton'); a ? a.remove() : null; return false;"}
		{/if}
		{exportmenu table=true right=true}
	</aside>

	<ul class="sub">
		<li{if $period == 'week'} class="current"{/if}><a href="?period=week&group={$group}&{$filters_uri}">Par semaine</a></li>
		<li{if $period == 'month'} class="current"{/if}><a href="?period=month&group={$group}&{$filters_uri}">Par mois</a></li>
		<li{if $period == 'year'} class="current"{/if}><a href="?period=year&group={$group}&{$filters_uri}">Par année</a></li>
		<li{if $period == 'accounting'} class="current"{/if}><a href="?period=accounting&group={$group}&{$filters_uri}">Par exercice</a></li>
	</ul>
	<ul class="sub">
		<li{if $group === 'task'} class="current"{/if}><a href="?period={$period}&{$filters_uri}">Par catégorie</a></li>
		<li{if $group === 'user'} class="current"{/if}><a href="?period={$period}&group=user&{$filters_uri}">Par personne</a></li>
	</ul>

	{include file="./_filters.tpl"}
</nav>

{if !$list->count()}
	<p class="alert block">Aucune tâche n'a été trouvée.</p>
{else}
	{include file="common/dynamic_list_head.tpl"}

	{foreach from=$list->iterate() item="row"}
		<tr style="{if $row.group === 'total'}font-weight: bold{/if}">
			<th>
				{if $row.header}
					<h3>
					{if $period == 'week'}{$row.year} — S{$row.week}
					{elseif $period == 'month'}{$row.date|strftime:'%B %Y'}
					{elseif $period == 'accounting'}{$row.period}
					{else}{$row.period}{/if}
					</h3>
				{/if}
			</th>
			<td>
				{if $group === 'user' && $row.user_id}
					{link href="all.php?id_user=%d&%s"|args:$row.user_id:$filters_uri label=$row.group}
				{elseif $row.task_id}
					{link href="all.php?id_task=%d&%s"|args:$row.task_id:$filters_uri label=$row.group}
				{elseif $row.group === 'total'}
					<strong>Total</strong>
				{else}
					— Non spécifié —
				{/if}
			</td>
			<td class="num">{$row.duration|taima_minutes}</td>
			<td class="num">{$row.etp}</td>
			<td class="actions"></td>
		</tr>
	{/foreach}
	</tbody>
	</table>
{/if}


{include file="_foot.tpl"}