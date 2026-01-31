{include file="_head.tpl" title="%s — %s"|args:$form.org_name:$form.name}

{include file="./_menu.tpl" current="home" current_sub="orders" show_export=true}

{include file="common/dynamic_list_head.tpl"}

	{foreach from=$list->iterate() item="row"}

		<tr>
			<th class="num"><a href="order.php?id={$row.id}">{$row.id}</a></th>
			<td>{$row.date|date}</td>
			<td class="money">{$row.amount|money_currency|raw}</td>
			<td>{$row.person}</td>
			<td>{if $row.status}Payé{else}Paiement incomplet{/if}</td>
			<td>{if $row.id_user}{linkbutton shape="user" label="Fiche membre" href="!users/details.php?id=%d"|args:$row.id_user}{/if}</td>
			<td class="actions">
				{linkbutton href="order.php?id=%s"|args:$row.id shape="help" label="Détails"}
			</td>
		</tr>

	{/foreach}

	</tbody>
</table>

{$list->getHTMLPagination()|raw}

{include file="_foot.tpl"}
