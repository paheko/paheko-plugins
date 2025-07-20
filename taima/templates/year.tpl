{include file="_head.tpl" title="Mon résumé"}

{include file="./_nav.tpl" current="year"}

{if !empty($weeks)}
	<h2 class="ruler">Par mois</h2>
	<table class="list">
		<thead>
			<tr>
				<th>Mois</th>
				<td>Durée</td>
				<td>Objectif</td>
				<td>Équivalent temps plein {$legal_hours.hours}h</td>
				<td>Nombre de tâches</td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$months item="row"}
			<tr>
				<th>{$row.date|taima_date:'MMMM yyyy'}</th>
				<td>
					{size_meter total=$target['month']*60 value=$row.duration text=$row.duration|taima_minutes}
				</td>
				<td><?=round(100 * ($row->duration / ($target['month']*60)))?>%</td>
				<td><?=round($row->duration / ($legal_hours['month']*60), 2)?></td>
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
				<td>Objectif</td>
				<td>Équivalent temps plein {$legal_hours.hours}h</td>
				<td>Nombre de tâches</td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$weeks item="row"}
			<tr>
				<th><a href="./?day={$row.first|date:'Y-m-d'}">{$row.year} — S{$row.week}</a></th>
				<td>{$row.first|taima_date:'d MMMM yyyy'} — {$row.last|taima_date:'d MMMM yyyy'}</td>
				<td>
					{size_meter total=$target['week']*60 value=$row.duration text=$row.duration|taima_minutes}
				</td>
				<td><?=round(100 * ($row->duration / ($target['week']*60)))?>%</td>
				<td><?=round($row->duration / ($legal_hours['week']*60), 2)?></td>
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
				<td>Objectif</td>
				<td>Équivalent temps plein {$legal_hours.hours}h</td>
				<td>Nombre de tâches</td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$years item="row"}
			<tr>
				<th>{link label=$row.year href="?year=%d&target_hours=%d"|args:$row.year:$target.hours}</th>
				<td>
					{size_meter total=$target['year']*60 value=$row.duration text=$row.duration|taima_minutes}
				</td>
				<td><?=round(100 * ($row->duration / ($target['year']*60)))?>%</td>
				<td><?=round($row->duration / ($legal_hours['year']*60), 3)?></td>
				<td>{$row.entries}</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
	<form method="get" action="">
		<fieldset>
			<legend>Objectif personnel</legend>
			<p class="help">
				Permet d'indiquer un nombre d'heures hebdomadaires qu'on désire utiliser comme valeur de comparaison.<br />
				Le calcul annuel prend en compte 6 semaines de congés, et 11 jours fériés.
			</p>
			<dl>
				{input type="number" label="Nombre d'heures par semaine" name="target_hours" required=true default=$target['hours']}
			</dl>
			<p>
				{button type="submit" label="Mettre à jour" shape="right"}
			</p>
		</fieldset>
	</form>

{else}
	<p class="help">Aucune activité pour le moment.</p>
{/if}

{include file="_foot.tpl"}