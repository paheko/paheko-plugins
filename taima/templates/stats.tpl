{include file="_head.tpl" title="Suivi du temps"}

{include file="./_nav.tpl" current="stats"}

<nav class="tabs">
	{if !$filters_uri}
		<aside>
			{linkbutton shape="search" href="#" id="filterFormButton" label="Filtrer par dates" onclick="var a = $('#filterForm'); a.disabled = false; g.toggle(a, true); this.remove(); var a = $('#compareFormButton'); a ? a.remove() : null; return false;"}
			{exportmenu table=true right=true}
		</aside>
	{/if}

	<ul class="sub">
		<li{if $period == 'week'} class="current"{/if}><a href="?p=week&g={$group}{$filters_uri}">Par semaine</a></li>
		<li{if $period == 'month'} class="current"{/if}><a href="?p=month&g={$group}{$filters_uri}">Par mois</a></li>
		<li{if $period == 'year'} class="current"{/if}><a href="?p=year&g={$group}{$filters_uri}">Par année</a></li>
		<li{if $period == 'accounting'} class="current"{/if}><a href="?p=accounting&g={$group}{$filters_uri}">Par exercice</a></li>
	</ul>
	<ul class="sub">
		<li{if $group === 'task'} class="current"{/if}><a href="?p={$period}{$filters_uri}">Par catégorie</a></li>
		<li{if $group === 'user'} class="current"{/if}><a href="?p={$period}&g=user{$filters_uri}">Par personne</a></li>
	</ul>

	<form method="get" action="" class="{if !$filters_uri}hidden {/if}noprint" id="filterForm">
		<input type="hidden" name="p" value="{$period}" />
		<input type="hidden" name="g" value="{$group}" />
		<fieldset>
			<legend>Filtrer par date</legend>
			<p>
				<label for="f_after">Du</label>
				{input type="date" name="start" default=$filters.start}
				<label for="f_before">au</label>
				{input type="date" name="end" default=$filters.end}
				{button type="submit" label="Filtrer" shape="right"}
				<input type="submit" value="Annuler" onclick="this.form.querySelectorAll('input:not([type=hidden]), select').forEach((a) => a.disabled = true); this.form.submit();" />
			</p>
		</fieldset>
	</form>
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
					{link href="all.php?id_user=%d"|args:$row.user_id label=$row.group}
				{elseif $row.task_id}
					{link href="all.php?id_task=%d"|args:$row.task_id label=$row.group}
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