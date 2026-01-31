{include file="_head.tpl" title="Gestion moyens de paiement"}

{include file="../_nav.tpl" current='methods'}

{include file="common/dynamic_list_head.tpl"}
		{foreach from=$list->iterate() item="method"}
			<tr>
				<th>{$method.name}</th>
				{if $has_locations}
				<td>{if !$method.location}{tag label="Aucun"}{else}{$method.location}{/if}</td>
				{/if}
				<td>{$method.type}</td>
				<td>{$method.account}</td>
				<td>{if $method.enabled}{tag label="Activé" color="darkgreen"}{else}{tag label="Désactivé" color="#999"}{/if}</td>
				<td class="actions">
					{linkbutton href="products.php?id=%d"|args:$method.id label="Produits" shape="menu"}
					{linkbutton href="edit.php?id=%d"|args:$method.id label="Modifier" shape="edit"}
					{linkbutton href="edit.php?id=%d&delete"|args:$method.id label="Supprimer" shape="delete" target="_dialog"}
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>

{$list->getHTMLPagination()|raw}

{include file="_foot.tpl"}