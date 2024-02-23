{include file="_head.tpl" title="Gestion produits"}

{include file="../_nav.tpl" current='products'}

<table class="list">
	<thead>
		<tr>
			<th>Nom</th>
			<td></td>
			<td>Prix</td>
			<td>Quantité par défaut</td>
			<td>Stock actuel</td>
			<td></td>
		</tr>
	</thead>
	{foreach from=$list key="category" item="products"}
		<tbody>
			<tr>
				<th colspan="6"><h2 class="ruler">{$category}</h2></th>
			</tr>
			{foreach from=$products item="product"}
				<tr>
					<th>{$product.name}</th>
					<td>{if $product.image}{*TODO*}{/if}</td>
					<td class="money">{if $product.price < 0}<span class="alert">{/if}{$product.price|escape|money_currency}{if $product.price < 0}</span>{/if}</td>
					<td class="num">{$product.qty}</td>
					<td class="num">{$product.stock}</td>
					<td class="actions">
						{linkbutton href="history.php?id=%d"|args:$product.id label="Historique" shape="calendar"}
						{*linkbutton shape="image" href="!common/files/upload.php?p=%s"|args:$product.images_path target="_dialog" label="Photo"*}
						{linkbutton href="edit.php?id=%d"|args:$product.id label="Modifier" shape="edit"}
						{linkbutton href="edit.php?id=%d&delete"|args:$product.id label="Supprimer" shape="delete" target="_dialog"}
					</td>
				</tr>
			{/foreach}
		</tbody>
	{/foreach}
</table>

{include file="_foot.tpl"}