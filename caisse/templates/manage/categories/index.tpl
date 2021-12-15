{include file="admin/_head.tpl" title="Gestion produits" current="plugin_%s"|args:$plugin.id}

{include file="%s/manage/_nav.tpl"|args:$pos_templates_root current='categories'}

<table class="list">
	<thead>
		<tr>
			<th>Nom</th>
			<td>Compte</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$list item="category"}
			<tr>
				<th>{$category.name}</th>
				<td>{$category.account}</td>
				<td class="actions">
					{linkbutton href="edit.php?id=%d"|args:$category.id label="Modifier" shape="edit" target="_dialog"}
					{linkbutton href="edit.php?id=%d&delete"|args:$category.id label="Supprimer" shape="delete" target="_dialog"}
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>

{include file="admin/_foot.tpl"}