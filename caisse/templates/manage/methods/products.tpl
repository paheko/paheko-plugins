{include file="admin/_head.tpl" title="Produits associés à '%s'"|args:$method.name current="plugin_%s"|args:$plugin.id}

{include file="%s/manage/_nav.tpl"|args:$pos_templates_root current='methods'}

<form method="post" action="">

<p class="help">Cocher les cases des produits qui peuvent être réglés avec ce moyen de paiement. Si un produit n'est pas coché, il ne sera pas possible de le régler avec ce moyen de paiement.</p>

<table class="list">
	<thead>
		<tr>
			<td class="check"><input type="checkbox" title="Tout cocher / décocher" id="f_all" /><label for="f_all"></label></td>
			<th>Nom</th>
			<td class="money">Prix</td>
		</tr>
	</thead>
	<tbody>
		<?php $cat = null; ?>
		{foreach from=$list item="product"}
		{if $cat != $product.category_name}
			<?php $cat = $product->category_name; ?>
			<tr>
				<td colspan="4"><h2 class="ruler">{$cat}</h2></td>
			</tr>
		{/if}
			<tr>
				<td class="check">{input type="checkbox" name="products[%d]"|args:$product.id value="1" default=$product.checked}</td>
				<th>{$product.name}</th>
				<td class="money">{$product.price|raw|money_currency}</td>
			</tr>
		{/foreach}
	</tbody>
</table>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
	</p>


</form>

{include file="admin/_foot.tpl"}