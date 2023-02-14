{include file="_head.tpl" title="%s — %s"|args:$form.org_name,$form.name}

{include file="./_menu.tpl" current="home" current_sub="orders" show_export=true}

{include file="common/dynamic_list_head.tpl"}

	{foreach from=$list->iterate() item="row"}

		<tr>
			<th class="num"><a href="order.php?id={$row.id}">{$row.id}</a></th>
			<td>{$row.date|date}</td>
			<td class="money">{$row.amount|money_currency|raw}</td>
			<td>{$row.label}</td>
			<td>{if $row.status}Payé{else}Paiement incomplet{/if}</td>
			<td class="actions">
				{linkbutton href="order.php?id=%s"|args:$row.id shape="help" label="Détails"}
			</td>
		</tr>

	{/foreach}

	</tbody>
</table>

{pagination url=$list->paginationURL() page=$list.page bypage=$list.per_page total=$list->count()}

{include file="_foot.tpl"}
