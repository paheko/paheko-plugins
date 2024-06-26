{include file="_head.tpl" title="Statistiques web"}

<p class="help block">Note : les visites des robots et moteurs de recherche ne sont pas prises en compte.</p>

<figure>
	<img src="?graph" alt="Graphique des statistiques par mois" />
</figure>

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
			<th><a href="{$www_url}{$row.uri}" target="_blank">{if !$row.uri}— Page d'accueil —{else}{$row.uri}{/if}</a></th>
			<td class="num">{$row.hits}</td>
		</tr>
		{/foreach}
	</tbody>
</table>

{include file="_foot.tpl"}
