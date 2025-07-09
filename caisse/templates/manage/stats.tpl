{include file="_head.tpl" title=$title}

{include file="./_nav.tpl" current='stats'}

<nav class="tabs">
	<ul class="sub">
		{foreach from=$years item="y"}
		<li class="{if $year === $y}current{/if}">{link href="?year=%d&page=%s&period=%s"|args:$y:$page:$period label=$y}</li>
		{/foreach}
	</ul>

	{if $year}
	<ul class="sub">
		<li class="{if $page === 'sales_categories'}current{/if}">{link href="?year=%d&page=sales_categories&period=%s"|args:$year:$period label="Ventes, par catégorie"}</li>
		<li class="{if $page === 'sales_products'}current{/if}">{link href="?year=%d&page=sales_products&period=%s"|args:$year:$period label="Ventes, par produit"}</li>
		<li class="{if $page === 'methods_in'}current{/if}">{link href="?year=%d&page=methods_in&period=%s"|args:$year:$period label="Encaissements"}</li>
		<li class="{if $page === 'methods_out'}current{/if}">{link href="?year=%d&page=methods_out&period=%s"|args:$year:$period label="Décaissements"}</li>
		<li class="{if $page === 'tabs'}current{/if}">{link href="?year=%d&page=tabs&period=%s"|args:$year:$period label="Notes"}</li>
	</ul>

	<ul class="sub">
		<li class="{if $period === 'year'}current{/if}">{link href="?year=%d&page=%s&period=year"|args:$year:$page label="Sur l'année"}</li>
		<li class="{if $period === 'semester'}current{/if}">{link href="?year=%d&page=%s&period=semester"|args:$year:$page label="Par semestre"}</li>
		<li class="{if $period === 'quarter'}current{/if}">{link href="?year=%d&page=%s&period=quarter"|args:$year:$page label="Par trimestre"}</li>
		<li class="{if $period === 'month'}current{/if}">{link href="?year=%d&page=%s&period=month"|args:$year:$page label="Par mois"}</li>
		<li class="{if $period === 'day'}current{/if}">{link href="?year=%d&page=%s&period=day"|args:$year:$page label="Par jour"}</li>
		<li class="{if $period === 'all'}current{/if}">{link href="?year=%d&page=%s&period=all"|args:$year:$page label="Tout"}</li>
	</ul>
	{/if}
</nav>

{if $list}

	{if $page === 'methods_in' && $period === 'year'}
		<section class="graphs">
			<figure>
				<figcaption><h2>Montant des encaissements, par méthode et par mois</h2></figcaption>
				<img src="?graph=methods&year={$year}"/>
			</figure>
		</section>
	{elseif $page === 'sales_categories' && $period === 'year'}
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

	<p class="actions">
		{exportmenu right=true}
	</p>

	{include file="common/dynamic_list_head.tpl"}
		{foreach from=$list->iterate() item="row"}
			<tr>
			{foreach from=$row key="key" item="value"}
				<td>
				{if $key === 'period' && $period === 'month'}
					{$value|strftime:'%m - %B'}
				{elseif $key === 'date'}
					{$value|date_short:true}
				{elseif $key === 'sum' || $key === 'price'}
					{$value|escape|money_currency}
				{elseif $key === 'weight'}
					{$value|weight:false:true}
				{elseif $key === 'tab'}
					{link href="../tab.php?id=%d"|args:$value label=$value class="num"}
				{elseif $key === 'avg_open_time' || $key === 'avg_close_time'}
					<?php $h = floor($value); $value = sprintf('%02d', $h) . ':' . sprintf('%02d', ($value - $h)*60); ?>
					{$value}
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

	{$list->getHTMLPagination()|raw}

{else}

	<p class="help">
		Merci de sélectionner un choix ci-dessus.
	</p>

{/if}

{include file="_foot.tpl"}