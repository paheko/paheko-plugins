{include file="_head.tpl" title="Gestion de la caisse" current="plugin_%s"|args:$plugin.id}

{include file="%s/manage/_nav.tpl"|args:$pos_templates_root current='stats'}

{if !$year}
<p class="help">Sélectionner une année ci-dessous.</p>
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

	<table class="list">
		<caption>Par mois et catégorie</caption>
		<thead>
			<tr>
				<th>Mois</th>
				<td>Catégorie</td>
				<td>Nombre de produits vendus</td>
				<td>Montant</td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$categories_per_month item="row"}
				<tr>
					<th>{$row.date|strftime:'%B'}</th>
					<td>{$row.category_name}</td>
					<td>{$row.count}</td>
					<td>{$row.sum|escape|money_currency}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/if}

{include file="_foot.tpl"}