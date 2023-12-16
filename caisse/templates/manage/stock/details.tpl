{include file="_head.tpl" title="Stock : %s"|args:$event.label}

{include file="../_nav.tpl" current='stock'}

{if !$event.applied}
<p class="help">
	Sélectionner des produits à droite, puis indiquer {if $event.type == $event::TYPE_INVENTORY}leur stock actuel{else}le changement de stock à effectuer{/if} dans la colonne de gauche.<br />

	Terminer en appliquant les changements. Une fois les changements appliqués, il n'est plus possible de modifier les quantités.<br /><br />
	Note : seuls sont affichés les produits dont le champ &quot;stock&quot; n'est pas vide.
</p>
{/if}

{form_errors}

<section class="pos">
	<section class="tab">
		<section class="items">
		<form method="post" action="">
			{csrf_field key=$csrf_key}
			<table class="list">
				<thead>
					<tr>
						<th>Produit</th>
						<td>Stock enregistré</td>
						<td>{if $event.type == $event::TYPE_INVENTORY}Stock inventorié{else}Changement de stock{/if}</td>
						<td>Valeur d'achat</td>
						<td></td>
					</tr>
				</thead>
				<tbody>
					{foreach from=$list item="row"}
						<tr>
							<th><small class="cat">{$row.category_name}</small> {$row.product_name}</th>
							<td>{$row.current_stock}</td>
							<td>
								{if $event.applied}
									{if $row.change > 0 && $event.type != $event::TYPE_INVENTORY}+{/if}{$row.change}
								{else}
									<button type="submit" class="change" name="change[{$row.product_id}]" value="{$row.change}">{if $row.change > 0 && $event.type != $event::TYPE_INVENTORY}+{/if}{$row.change}</button>
								{/if}
							</td>
							<td>{$row.value|raw|money_currency}</td>
							<td class="actions">
								{if !$event.applied}
								{linkbutton label="" shape="delete" href="?id=%d&delete=%d"|args:$event.id,$row.product_id title="Cliquer pour supprimer la ligne"}
								{/if}
							</td>
						</tr>
					{/foreach}
						<tr>
							<th>Total</th>
							<td>{$total.current_stock}</td>
							<td>
								{$total.change}
							</td>
							<td>{$total.value|raw|money_currency}</td>
							<td class="actions">
							</td>
						</tr>
				</tbody>
			</table>
		</form>
		{if !$event.applied && count($list)}
		<form method="post" action="" id="apply-changes">
			<p class="submit">
				{csrf_field key=$csrf_key}
				{button type="submit" name="apply" label="Appliquer les changements" shape="right" class="main"}
			</p>
		</form>
		{/if}
		</section>

	</section>

	{if !$event.applied}
	<section class="products">
		<input type="text" name="q" placeholder="Recherche rapide" autofocus />
		<form method="post" action="">
		{foreach from=$products_categories key="category" item="products"}
			<section>
				<h2 class="ruler">{$category}</h2>

				<div>
				{foreach from=$products item="product"}
					<button name="add[{$product.id}]" class="change" value="0">
						<h3>{$product.name}</h3>
						<h4>{$product.price|escape|money_currency}</h4>
					</button>
				{/foreach}
				</div>
			</section>
		{/foreach}
		{csrf_field key=$csrf_key}
		</form>
	</section>
	{/if}

</section>

<script type="text/javascript">
{literal}
function askChange() {
	var change = window.prompt('Nombre de produits ?');
	if (change == '') {
		return;
	}

	this.value = parseInt(change, 10);
}
$('button.change').forEach((e) => {
	e.onclick = askChange;
});
$('#apply-changes').onsubmit = (e) => {
	if (confirm('Une fois les modifications appliquées au stock, le stock sera modifié et cette page ne pourra plus être modifiée.')) {
		return true;
	}

	e.preventDefault();
	return false;
};
{/literal}
</script>

{include file="_foot.tpl"}