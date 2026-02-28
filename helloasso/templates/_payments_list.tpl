<?php $disable_user_sort = !$details; ?>
{include file="common/dynamic_list_head.tpl"}

	{foreach from=$list->iterate() item="row"}

		<tr>
			<th class="num">{if $details}{link href="order.php?id=%d"|args:$row.id_order label=$row.id}{else}{$row.id}{/if}</th>
			<td>{$row.date|date}</td>
			<td class="money">{$row.amount|money_currency|raw}</td>
			<td>{$row.person}</td>
			<td>{$row.state}</td>
			<td>{$row.transferred}</td>
			<td>{if $row.id_user}{linkbutton shape="user" label="Fiche membre" href="!users/details.php?id=%d"|args:$row.id_user}{/if}</td>
			{if $list->hasColumn('id_transaction')}
				<td>{if $row.id_transaction}{link class="num" label="#%d"|args:$row.id_transaction href="!acc/transactions/details.php?id=%d"|args:$row.id_transaction}{/if}</td>
			{/if}
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