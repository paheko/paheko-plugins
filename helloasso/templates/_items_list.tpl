
{include file="common/dynamic_list_head.tpl"}

	{foreach from=$list->iterate() item="row"}

		<tr>
			<td class="num"><a href="chargeable.php?id={$row.id_chargeable}">{$row.id_chargeable}</a></td>
			<td class="num">
				{if $row.transactions}
					{foreach from=$row.transactions item="id_transaction"}
						{link href="!acc/transactions/details.php?id=%d"|args:$id_transaction label="#%d"|args:$id_transaction}
					{/foreach}
				{/if}
			</td>
			<td class="money">{$row.amount|money_currency|raw}</td>
			<td>{$row.type}</td>
			<td>{$row.label}</td>
			<td class="num">{$row.person}</td>
			<td class="num">{if $row.id_user}{$row.user_name} <a href="{$admin_url}users/details.php?id={$row.id_user}">{$row.numero}</a>{/if}</td>
			<td>{$row.options|escape|nl2br}</td>
			{if property_exists($row, 'custom_fields')}
			<td>
				{if $row.custom_fields}
				<table>
					{foreach from=$row.custom_fields item="value" key="name"}
					<tr>
						<td>{$name}</td>
						<th>{$value}</th>
					</tr>
					{/foreach}
				</table>
				{/if}

			</td>
			{/if}
			<td>{if $row.service}{$row.service}{else}-{/if}</td>
			<td>{$row.state}</td>
			<td class="actions">
				{if $details}{linkbutton href="order.php?id=%s"|args:$row.id_order shape="help" label="Détails"}{/if}
			</td>
		</tr>

	{/foreach}

	</tbody>
</table>

{$count_opti->getHTMLPagination()|raw}

<p class="help block">
	* Est différent de la "personne" si il y a un doute que le/la payeur/euse soit la même personne (ex: courriel différent).
</p>