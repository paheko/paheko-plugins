{include file="_head.tpl" title="Liste des produits"}

<nav class="tabs">
	<aside>
		{if $archived}
			{linkbutton shape="eye" label="Voir les produits non archivés" href="?id=%d"|args:$id}
		{else}
			{linkbutton shape="eye-off" label="Voir seulement les produits archivés" href="?id=%d&archived=1"|args:$id}
		{/if}
	</aside>
</nav>

<form action="" method="get" class="shortFormLeft">
	<p>{input type="search" name="q" placeholder="Nom du produit" default=$search} {button type="submit" label="Chercher" shape="right"}</p>
</form>

{include file="common/dynamic_list_head.tpl"}
	<?php $category = null; ?>
		{foreach from=$list->iterate() item="row"}
		<tr>
			<td>
				{if $category !== $row.category}
					<?php $category = $row->category; ?>
					{$row.category}
				{/if}
			</td>
			<th scope="row">{$row.name}</th>
			<td class="actions">
				{button shape="plus" label="Ajouter" onclick="window.parent.g.inputListSelected(this.dataset.id, this.dataset.name);" data-id=$row.id data-name=$row.name}
			</td>
		</tr>
	{/foreach}
	</tbody>
</table>

{$list->getHTMLPagination()|raw}

{include file="_foot.tpl"}