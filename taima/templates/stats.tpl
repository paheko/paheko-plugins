{include file="_head.tpl" title="Suivi du temps"}

{include file="./_nav.tpl" current="stats"}

<nav class="tabs">
	{if !$filter_dates}
		<aside>
			{linkbutton shape="search" href="#" id="filterFormButton" label="Filtrer par dates" onclick="var a = $('#filterForm'); a.disabled = false; g.toggle(a, true); this.remove(); var a = $('#compareFormButton'); a ? a.remove() : null; return false;"}
			{exportmenu table=true right=true}
		</aside>
	{/if}

	<ul class="sub">
		<li{if $grouping == 'week'} class="current"{/if}><a href="?g=week{if $per_user}&amp;per_user=1{/if}{$filter_dates}">Par semaine</a></li>
		<li{if $grouping == 'month'} class="current"{/if}><a href="?g=month{if $per_user}&amp;per_user=1{/if}{$filter_dates}">Par mois</a></li>
		<li{if $grouping == 'year'} class="current"{/if}><a href="?g=year{if $per_user}&amp;per_user=1{/if}{$filter_dates}">Par année</a></li>
		<li{if $grouping == 'accounting'} class="current"{/if}><a href="?g=accounting{if $per_user}&amp;per_user=1{/if}{$filter_dates}">Par exercice</a></li>
	</ul>
	<ul class="sub">
		<li{if !$per_user} class="current"{/if}><a href="?g={$grouping}{$filter_dates}">Par tâche</a></li>
		<li{if $per_user} class="current"{/if}><a href="?g={$grouping}&amp;per_user=1{$filter_dates}">Par personne</a></li>
	</ul>

	<form method="get" action="" class="{if !$filter_dates}hidden {/if}noprint" id="filterForm">
		<input type="hidden" name="g" value="{$grouping}" />
		<input type="hidden" name="per_user" value="{$per_user}" />
		<fieldset>
			<legend>Filtrer par date</legend>
			<p>
				<label for="f_after">Du</label>
				{input type="date" name="start" default=$start}
				<label for="f_before">au</label>
				{input type="date" name="end" default=$end}
				{button type="submit" label="Filtrer" shape="right"}
				<input type="submit" value="Annuler" onclick="this.form.querySelectorAll('input:not([type=hidden]), select').forEach((a) => a.disabled = true); this.form.submit();" />
			</p>
		</fieldset>
	</form>
</nav>

<table class="list auto">
	{foreach from=$per_week item="week"}
	<tbody>
		<tr>
			<th colspan="3">
				<h2 class="ruler">
				{if $grouping == 'week'}{$week.year} — S{$week.week}
				{elseif $grouping == 'month'}{$week.date|strftime:'%B %Y'}
				{elseif $grouping == 'accounting'}{$week.year_label}
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


{include file="_foot.tpl"}