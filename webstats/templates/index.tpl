{include file="admin/_head.tpl" title="Statistiques web" current="plugin_%s"|args:$plugin.id}

<p class="help block">Note : les visites des robots et moteurs de recherche ne sont pas prises en compte.</p>

<h2 class="ruler">Statistiques par mois</h2>

<table class="list">
	<thead>
		<tr>
			<th>Mois</th>
			<td class="num">Visites</td>
			<td class="num">Dont mobiles</td>
			<td class="num">Pages vues</td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$stats item="row"}
		<tr>
			<th>{$row.date|strftime:"%B %Y"}</th>
			<td class="num">{$row.visits}</td>
			<td class="num">{$row.mobile_visits}</td>
			<td class="num">{$row.hits}</td>
		</tr>
		{/foreach}
	</tbody>
</table>

<h2 class="ruler">Pages les plus vues</h2>

<table class="list">
	<thead>
		<tr>
			<th>Page</th>
			<td>Nombre de vues</td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$hits item="row"}
		<tr>
			<th><a href="{$www_url}{$row.uri}" target="_blank">{$row.uri}</a></th>
			<td class="num">{$row.hits}</td>
		</tr>
		{/foreach}
	</tbody>
</table>

{include file="admin/_foot.tpl"}
