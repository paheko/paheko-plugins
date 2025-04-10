{include file="_head.tpl" title="Stock des produits"}

{include file="../_nav.tpl" current='stock' subcurrent="products"}

<p class="actions">
	{exportmenu right=true table=true}
</p>

<table class="list">
	<thead>
		<tr>
			<th>Nom</th>
			<td>Prix unitaire</td>
			<td>En stock</td>
			<td>Valeur commerciale<br />(prix de vente)</td>
			<td>Valeur du stock<br />(prix d'achat)</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
	<?php $category = null; ?>
	{foreach from=$list item="product"}
		{if $category !== $product.category}
			<?php $category = $product->category; $c = $categories[$category]; ?>
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