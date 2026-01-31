{include file="_head.tpl" title="Stock des produits"}

{include file="../_nav.tpl" current='stock' subcurrent="products"}

<nav class="tabs">
	<aside>
		{if $archived}
			{linkbutton shape="eye" label="Voir les produits non archivés" href="?"}
		{else}
			{linkbutton shape="eye-off" label="Voir seulement les produits archivés" href="?archived=1"}
		{/if}
		{exportmenu right=true}
	</aside>
</nav>

<form action="" method="get" class="shortFormLeft">
	<p>{input type="search" name="q" placeholder="Nom du produit" default=$search} {button type="submit" label="Chercher" shape="right"}</p>
</form>


<table class="list">
	<thead>
		<tr>
			<th>Produit</th>
			<td>Prix unitaire</td>
			<td>Quantité par défaut</td>
			<td>Valeur à la vente</td>
			<td>Valeur du stock (à l'achat)</td>
			<td></td>
		</tr>
	</thead>
	<?php $category = null; ?>
	{foreach from=$list->iterate() item="product"}
		{if $category !== $product.id_category}
			<?php $category = $product->id_category; $c = $categories[$category]; ?>
			<tr>
				<th colspan="2"><h2 class="ruler">{$c.label}</h2></th>
				<td class="num">{$c.stock}</td>
				<td class="money">{$c.sale_value|escape|money_currency}</td>
				<td class="money">{$c.stock_value|escape|money_currency}</td>
				<td></td>
			</tr>
		{/if}
			<tr>
				<th>{$product.name}</th>
				<td class="money">{$product.price|escape|money_currency}</td>
				<td class="num">
					{if $product.stock < 0}{tag color="darkred" label=$product.stock}
					{elseif $product.stock <= 10}{tag color="darkorange" label=$product.stock}
					{else}{tag color="darkgreen" label=$product.stock}{/if}
				</td>
				<td class="money">{$product.sale_value|escape|money_currency}</td>
				<td class="money">{$product.stock_value|escape|money_currency}</td>
				<td class="actions">
					{linkbutton href="../products/history.php?id=%d"|args:$product.id label="Historique" shape="calendar"}
				</td>
			</tr>
	{/foreach}
	</tbody>
	<tfoot>
		<tr>
			<th colspan="2"><h2 class="ruler">Total</h2></th>
			<td class="num">{$categories.total.stock}</td>
			<td class="money">{$categories.total.sale_value|escape|money_currency}</td>
			<td class="money">{$categories.total.stock_value|escape|money_currency}</td>
			<td></td>
		</tr>
	</tfoot>
</table>

{include file="_foot.tpl"}