
{include file="common/dynamic_list_head.tpl"}

	{foreach from=$list->iterate() item="row"}

		<tr>
			<th class="num"><a href="order.php?id={$row.id_order}">{$row.id}</a></th>
			<td class="money">{$row.amount|money_currency|raw}</td>
			<td>{$row.type}</td>
			<td>{$row.label}</td>
			<td>{$row.person}</td>
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
			<td>{$row.state}</td>
			<td class="actions">
				{if $details}{linkbutton href="order.php?id=%s"|args:$row.id_order shape="help" label="Détails"}{/if}
			</td>
		</tr>



	{/foreach}

	</tbody>
</table>
