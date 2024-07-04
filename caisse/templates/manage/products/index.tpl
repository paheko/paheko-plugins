{include file="_head.tpl" title="Gestion produits"}

{include file="../_nav.tpl" current='products'}

<table class="list">
	<thead>
		<tr>
			<th>Nom</th>
			<td></td>
			<td>Prix</td>
			<td>Quantité par défaut</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
	<?php $category = null; ?>
	{foreach from=$list item="product"}
		{if $category !== $product.category}
			<?php $category = $product->category; ?>
			<tr>
				<th colspan="5"><h2 class="ruler">{$product.category_name}</h2></th>
			</tr>
		{/if}
		<tr>
			<th>{$product.name}</th>
			<td>{if $product.image}{*TODO*}{/if}</td>
			<td class="money">{if $product.price < 0}<span class="alert">{/if}{$product.price|escape|money_currency}{if $product.price < 0}</span>{/if}</td>
			<td class="num">{$product.qty}</td>
			<td class="actions">
				{if $product.stock !== null}{linkbutton href="history.php?id=%d"|args:$product.id label="Historique du stock" shape="calendar"}{/if}
				{*linkbutton shape="image" href="!common/files/upload.php?p=%s"|args:$product.images_path target="_dialog" label="Photo"*}
				{linkbutton href="edit.php?id=%d"|args:$product.id label="Modifier" shape="edit"}
				{linkbutton href="edit.php?id=%d&delete"|args:$product.id label="Supprimer" shape="delete" target="_dialog"}
			</td>
		</tr>
	{/foreach}
	</tbody>
</table>

{include file="_foot.tpl"}