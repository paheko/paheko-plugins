{include file="admin/_head.tpl" title="Statistiques" current="plugin_%s"|args:$plugin.id}

<nav class="tabs">
	<ul>
		<li><a href="./">Caisse</a></li>
		<li class="current"><a href="{$self_url}">Statistiques</a></li>
	</ul>
</nav>

{if !$year}
<ul>
	{foreach from=$years item="year"}
	<li><a href="?year={$year}">{$year}</a></li>
	{/foreach}
</ul>
{else}
	<figure>
		<img src="?graph=methods&year={$year}"/>
		<figcaption>Paiements par méthode et par mois (en {$config.monnaie})</figcaption>
	</figure>
	<figure>
		<img src="?graph=categories&year={$year}"/>
		<figcaption>Ventes par catégorie et par mois (en {$config.monnaie})</figcaption>
	</figure>
	<figure>
		<img src="?graph=categories_qty&year={$year}"/>
		<figcaption>Volumes par catégorie et par mois</figcaption>
	</figure>

	<table class="list">
		<caption>Par mois et méthode de paiement</caption>
		<thead>
			<tr>
				<th>Mois</th>
				<td>Méthode</td>
				<td>Paiements</td>
				<td>Montant</td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$methods_per_month item="row"}
				<tr>
					<th>{$row.date|strftime:'%B'}</th>
					<td>{$row.method}</td>
					<td>{$row.count}</td>
					<td>{$row.sum|escape|money_currency}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/if}

{include file="admin/_foot.tpl"}