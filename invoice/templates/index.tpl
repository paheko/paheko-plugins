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
				<th>{$doc.label}</th>
				<td>{$doc.client_name}</td>
				<td>{tag label=$doc.status_label color=$doc.status_color}</td>
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