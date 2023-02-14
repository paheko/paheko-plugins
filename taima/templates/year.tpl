{include file="_head.tpl" title="Mon résumé"}

{include file="./_nav.tpl" current="year"}

{if empty($weeks)}
	<p class="help">Aucune activité pour le moment.</p>
{else}
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
					<progress value="{$week.duration}" max="1260"></progress>
					{$week.duration|taima_minutes}
				</td>
				<td>{$week.entries}</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
{/if}

{include file="_foot.tpl"}