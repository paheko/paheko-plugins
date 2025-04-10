{include file="_head.tpl" title="Historique produit : %s"|args:$product.name}

{include file="../_nav.tpl" current='history'}

<nav class="tabs">
	<ul class="sub">
		<li class="title">Produit : {$product.name}</li>
	</ul>
</nav>

{include file="common/dynamic_list_head.tpl"}
		{foreach from=$list->iterate() item="row"}
			<tr>
				<th>{$row.date|date}</th>
				<td>{$row.type}</td>
				<td>{$row.event_label}</td>
				<td class="num">
					{$row.change}
				</td>
				<td class="actions">
					{if $row.id_tab}
						{linkbutton href="%stab.php?id=%d"|args:$plugin_admin_url:$row.id_tab label="Note de caisse" shape="menu"}
					{elseif $row.id_event}
						{linkbutton href="%smanage/stock/details.php?id=%d"|args:$plugin_admin_url:$row.id_event label="Événement de stock" shape="table"}
					{/if}
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>

{$list->getHTMLPagination()|raw}

{include file="_foot.tpl"}