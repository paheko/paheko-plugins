{include file="admin/_head.tpl" title="Stock : %s"|args:$event.label current="plugin_%s"|args:$plugin.id}

{include file="%s/manage/_nav.tpl"|args:$pos_templates_root current='stock'}

<p>
	{linkbutton href="add_change.php?id=%d"|args:$event.id label="Ajouter un produit" shape="plus" target="dialog"}
</p>

<table class="list">
	<thead>
		<tr>
			<th>Produit</th>
			<td>Stock actuel</td>
			<td>Changement de stock</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$list item="row"}
			<tr>
				<th>{$row.product_label}</th>
				<td>{$row.current_stock}</td>
				<td>{$row.change}</td>
				<td class="actions">
					{linkbutton href="?id=%d&delete=%d"|args:$event.id,$row.id label="Supprimer" shape="delete"}
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>

{include file="admin/_foot.tpl"}