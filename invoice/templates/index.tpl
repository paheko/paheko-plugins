{include file="_head.tpl" title=$title current="plugin_invoice"}

{include file="./_nav.tpl" current=$current_tab}

{if $list->count()}
	{include file="common/dynamic_list_head.tpl"}
	{foreach from=$list->iterate() item="doc"}
		<tr>
			{if $list->hasColumn('type')}
				<td>{$doc.type_label}</td>
			{/if}
				<td class="num">{if !$doc.number}<em>(Brouillon)</em>{else}{$doc.number}{/if}</td>
				<td>{$doc.date_created|date_short}</td>
			{if $list->hasColumn('date_sent')}
				<td>{tag status=$doc.date_sent_status label=$doc.date_sent|relative_date_count}</td>
			{/if}
				<th>{$doc.label}</th>
				<td>{$doc.client_name}</td>
			{if $list->hasColumn('status')}
				<td>{tag label=$doc.status_label status=$doc.status_color}</td>
			{/if}
				<td class="money">{$doc.total|raw|money_currency_html:false}</td>
				<td class="actions">
					{linkbutton shape="menu" label="Détails" href="details.php?id=%d"|args:$doc.id}
				</td>
			</tr>
	{/foreach}
	</tbody>
	</table>
	{$list->getHTMLPagination()|raw}
{else}
	<p class="alert block">Il n'y a aucun document ici.</p>
{/if}

{include file="_foot.tpl"}