{include file="_head.tpl" title="Gestion de la caisse"}

{include file="./_nav.tpl" current='stats'}

{if !$year}
<p class="help">Sélectionner une année ci-dessous.</p>
<ul style="font-size: 2em; display: flex; flex-wrap: wrap; gap: 1em">
	{foreach from=$years item="year"}
	<li>{linkbutton href="?year=%d"|args:$year label=$year}</li>
	{/foreach}
</ul>
{else}
<section class="graphs">
	<figure>
		<figcaption><h2>Montant des encaissements, par méthode et par mois</h2></figcaption>
		<img src="?graph=methods&year={$year}"/>
	</figure>
	<figure>
		<figcaption><h2>Montant des ventes, par catégorie et par mois</h2></figcaption>
		<img src="?graph=categories&year={$year}"/>
	</figure>
	<figure>
		<figcaption><h2>Nombre de ventes par catégorie et par mois</h2></figcaption>
		<img src="?graph=categories_qty&year={$year}"/>
	</figure>
</section>

	<h2 class="ruler">Encaissements, par méthode et par mois</h2>
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

	<h2 class="ruler">Décaissements, par méthode et par mois</h2>
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
			{foreach from=$methods_out_per_month item="row"}
				<tr>
					<th>{$row.date|strftime:'%B'}</th>
					<td>{$row.method}</td>
					<td>{$row.count}</td>
					<td>{$row.sum|escape|money_currency}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>

	<h2 class="ruler">Ventes, par mois et par catégorie</h2>
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