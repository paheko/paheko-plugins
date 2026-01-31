{include file="_head.tpl" title="Gestion produits"}

{include file="../_nav.tpl" current='categories'}

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
					{linkbutton href="edit.php?id=%d"|args:$category.id label="Modifier" shape="edit"}
					{linkbutton href="edit.php?id=%d&delete"|args:$category.id label="Supprimer" shape="delete" target="_dialog"}
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>

{include file="_foot.tpl"}