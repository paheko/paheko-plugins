{include file="admin/_head.tpl" title="Mon résumé" plugin_css=['style.css'] current="plugin_taima"}

{include file="%s/templates/_nav.tpl"|args:$plugin_root current="year"}

<table class="list">
	<thead>
		<tr>
			<th>Semaine</th>
			<td>Dates</td>
			<td>Durée</td>
			<td>Nombre de tâches</td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$weeks item="week"}
		<tr>
			<th><a href="./?day={$week.first|date:'Y-m-d'}">{$week.year} — S{$week.week}</a></th>
			<td>{$week.first|taima_date:'d MMMM YYYY'} — {$week.last|taima_date:'d MMMM YYYY'}</td>
			<td>
				<progress value="{$week.duration}" max="2100"></progress>
				{$week.duration|taima_minutes}
			</td>
			<td>{$week.entries}</td>
		</tr>
		{/foreach}
	</tbody>
</table>

{include file="admin/_foot.tpl"}