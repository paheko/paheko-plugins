{include file="_head.tpl" title="Gestion de la caisse"}

{include file="./_nav.tpl" current='stats'}

{if !$year}

	<p class="help">Sélectionner une année ci-dessous.</p>
	<ul style="font-size: 2em; display: flex; flex-wrap: wrap; gap: 1em">
		{foreach from=$years item="year"}
		<li>{linkbutton href="?year=%d"|args:$year label=$year}</li>
		{/foreach}
	</ul>

{elseif $list}

	{if $page === 'methods_in'}
		<section class="graphs">
			<figure>
				<figcaption><h2>Montant des encaissements, par méthode et par mois</h2></figcaption>
				<img src="?graph=methods&year={$year}"/>
			</figure>
		</section>
	{elseif $page === 'sales_categories'}
		<section class="graphs">
			<figure>
				<figcaption><h2>Montant des ventes, par catégorie et par mois</h2></figcaption>
				<img src="?graph=categories&year={$year}"/>
			</figure>
			<figure>
				<figcaption><h2>Nombre de ventes par catégorie et par mois</h2></figcaption>
				<img src="?graph=categories_qty&year={$year}"/>
			</figure>
		</section>
	{/if}


	<h2 class="ruler">{$list->getTitle()}</h2>

	<p class="actions">
		{exportmenu right=true}
	</p>

	{include file="common/dynamic_list_head.tpl"}
		{foreach from=$list->iterate() item="row"}
			<tr>
			{foreach from=$row key="key" item="value"}
				<td>
				{if $key === 'month'}
					{$value|strftime:'%m - %B'}
				{elseif $key === 'sum'}
					{$value|escape|money_currency}
				{else}
					{$value}
				{/if}
				</td>
			{/foreach}
				<td></td>
			</tr>
		{/foreach}
		</tbody>
	</table>


{else}

	<h2 class="ruler">Année {$year}</h2>

	<dl class="large">
		<dt><a href="?year={$year}&amp;page=sales_categories">Ventes, par mois et par catégorie</a></dt>
		<dt><a href="?year={$year}&amp;page=sales_products_month">Ventes, par mois et par produit</a></dt>
		<dt><a href="?year={$year}&amp;page=sales_products_year">Ventes, par produit, sur toute l'année</a></dt>
		<dt><a href="?year={$year}&amp;page=methods_in">Encaissements, par mois et par méthode de paiement</a></dt>
		<dt><a href="?year={$year}&amp;page=methods_out">Décaissements, par mois et par méthode de paiement</a></dt>
	</dl>

{/if}

{include file="_foot.tpl"}