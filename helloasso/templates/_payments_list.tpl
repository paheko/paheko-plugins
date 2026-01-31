{include file="common/dynamic_list_head.tpl"}

	{foreach from=$list->iterate() item="row"}

		<tr>
			<th class="num"><a href="order.php?id={$row.id_order}">{$row.id}</a></th>
			<td class="num">{if $row.id_transaction}{link href="!acc/transactions/details.php?id=%d"|args:$row.id_transaction label="#%d"|args:$row.id_transaction}{/if}</td>
			<td>{$row.date|date}</td>
			<td class="money">{$row.amount|money_currency|raw}</td>
			<td>{$row.person}</td>
			<td>{$row.state}</td>
			<td>{$row.transferred}</td>
			<td class="actions">
				{if $row.receipt_url}
					{linkbutton href=$row.receipt_url target="_blank" shape="print" label="Attestation de paiement"}
				{/if}
				{if $details}{linkbutton href="order.php?id=%s"|args:$row.id_order shape="help" label="Détails"}{/if}
			</td>
		</tr>

	{/foreach}

	</tbody>
</table>