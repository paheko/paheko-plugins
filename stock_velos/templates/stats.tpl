{include file="_head.tpl" title="Statistiques"}

{include file="./_nav.tpl" current="stats"}

<figure>
	<img src="?graph=years" alt="" />
</figure>

<figure>
	<img src="?graph=exit" alt="" />
</figure>

<figure>
	<img src="?graph=entry" alt="" />
</figure>

<h2 class="ruler">Par année, source et raison de sortie</h2>

<table class="list">
	<tbody>
		{foreach from=$stats_years item="row"}
		<tr>
			<th>{$row.year}</th>
			<td>{$row.type}</td>
			<td>{$row.details}</td>
			<td>{$row.nb}</td>
			<td>{$row.poids|weight:true} kg</td>
		</tr>
		{/foreach}
	</tbody>
</table>

<h2 class="ruler">Par mois</h2>

<table class="list">
	<tbody>
		{foreach from=$stats_months item="row"}
		<tr>
			<th>{$row.month}</th>
			<td>{$row.type}</td>
			<td>{$row.details}</td>
			<td>{$row.nb}</td>
			<td>{$row.poids|weight:true} kg</td>
		</tr>
		{/foreach}
	</tbody>
</table>

{include file="_foot.tpl"}