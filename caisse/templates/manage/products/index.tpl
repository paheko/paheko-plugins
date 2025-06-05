{include file="_head.tpl" title="Gestion produits"}

{include file="../_nav.tpl" current='products'}

<p class="actions">
	{if $archived}
		{linkbutton shape="eye" label="Voir les produits non archivés" href="?"}
	{else}
		{linkbutton shape="eye-off" label="Voir seulement les produits archivés" href="?archived=1"}
	{/if}
</p>

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
			<td class="money">{if $row.price < 0}<span class="alert">{/if}{$row.price|escape|money_currency}{if $row.price < 0}</span>{/if}</td>
			<td class="num">{$row.qty}</td>
			<td class="actions">
				{if $row.stock !== null}{linkbutton href="history.php?id=%d"|args:$row.id label="Historique du stock" shape="calendar"}{/if}
				{linkbutton href="edit.php?id=%d"|args:$row.id label="Modifier" shape="edit"}
				{linkbutton href="edit.php?id=%d&delete"|args:$row.id label="Supprimer" shape="delete" target="_dialog"}
			</td>
		</tr>
	{/foreach}
	</tbody>
</table>

{$list->getHTMLPagination()|raw}

{include file="_foot.tpl"}