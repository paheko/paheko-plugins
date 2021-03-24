{include file="admin/_head.tpl" title="Gestion produits" current="plugin_%s"|args:$plugin.id}

<nav class="tabs">
	<ul>
		<li><a href="./">Caisse</a></li>
		<li class="current"><a href="{$self_url}">Gestion des produits</a></li>
		<li><a href="product_edit.php?new">Nouveau produit</a></li>
	</ul>
</nav>

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
					<td>{if $product.image}<img src="{$product.image|image_base64}" alt="" />{/if}</td>
					<td>{$product.price|escape|pos_money}</td>
					<td class="num">{$product.qty}</td>
					<td class="num">{$product.stock}</td>
					<td class="actions">
						{linkbutton href="product_edit.php?id=%d"|args:$product.id label="Modifier" shape="edit"}
						{*{linkbutton href="product_delete.php?id=%d"|args:$product.id label="Supprimer" shape="delete"}*}
					</td>
				</tr>
			{/foreach}
		</tbody>
	{/foreach}
</table>

{include file="admin/_foot.tpl"}