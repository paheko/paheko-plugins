<?php $disable_user_sort = !$details; ?>
{include file="common/dynamic_list_head.tpl"}

	{foreach from=$list->iterate() item="row"}

		<tr>
			<td>{tag label=$row.type_label color=$row.type_color}</td>
			<td class="num">{if $details}{link href="order.php?id=%d"|args:$row.id_order label=$row.id}{else}{$row.id}{/if}</td>
			<th scope="row">{$row.label}</th>
			<td class="money">{$row.amount|money_currency|raw}</td>
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
			<td>{$row.state_label}</td>
			<td class="actions">
				{if $details}
					{linkbutton href="order.php?id=%s"|args:$row.id_order shape="help" label="Détails"}
				{else}
					{if $row.card_url}
						{linkbutton href=$row.card_url shape="print" label="Carte d'adhérent" target="_blank"}
					{/if}
				{/if}

			</td>
		</tr>

		{if $row.options}
			{foreach from=$row.options item="option"}
			<tr>
				<td>{icon shape="right"} {tag label="Option" color="DarkMagenta"}</td>
				<td class="num">{$option.id}</td>
				<th scope="row">{$option.label}</th>
				<td class="money">{$option.amount|money_currency:false|raw}</td>
				<td></td>
				<td>
					{if $option.custom_fields}
					<table>
						{foreach from=$option.custom_fields item="value" key="name"}
						<tr>
							<td>{$name}</td>
							<th>{$value}</th>
						</tr>
						{/foreach}
					</table>
					{/if}
				</td>
				<td></td>
				<td class="actions">
				</td>
			</tr>
			{/foreach}
		{/if}

	{/foreach}

	</tbody>
</table>
