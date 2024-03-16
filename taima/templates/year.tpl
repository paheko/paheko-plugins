{include file="_head.tpl" title="Mon résumé"}

{include file="./_nav.tpl" current="year"}

{if !empty($weeks)}
	<h2 class="ruler">Par mois</h2>
	<table class="list">
		<thead>
			<tr>
				<th>Mois</th>
				<td>Durée</td>
				<td>Nombre de tâches</td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$months item="row"}
			<tr>
				<th>{$row.date|taima_date:'MMMM YYYY'}</th>
				<td>
					{size_meter total=2940 value=$row.duration text=$row.duration|taima_minutes}
				</td>
				<td>{$row.entries}</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
	<h2 class="ruler">Par semaine</h2>
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
			{foreach from=$weeks item="row"}
			<tr>
				<th><a href="./?day={$row.first|date:'Y-m-d'}">{$row.year} — S{$row.week}</a></th>
				<td>{$row.first|taima_date:'d MMMM YYYY'} — {$row.last|taima_date:'d MMMM YYYY'}</td>
				<td>
					{size_meter total=780 value=$row.duration text=$row.duration|taima_minutes}
				</td>
				<td>{$row.entries}</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
{elseif !empty($years)}
	<table class="list">
		<thead>
			<tr>
				<th>Année</th>
				<td>Durée</td>
				<td>Nombre de tâches</td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$years item="row"}
			<tr>
				<th>{link label=$row.year href="?year=%d"|args:$row.year}</th>
				<td>
					{size_meter total=35820 value=$row.duration text=$row.duration|taima_minutes}
				</td>
				<td>{$row.entries}</td>
			</tr>
			{/foreach}
		</tbody>
	</table>{else}
	<p class="help">Aucune activité pour le moment.</p>
{/if}

{include file="_foot.tpl"}