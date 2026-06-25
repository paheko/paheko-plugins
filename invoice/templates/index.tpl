{include file="_head.tpl" title=$title current="plugin_invoice"}

{include file="./_nav.tpl" current=$current_tab}

{if $list->count()}
	{include file="common/dynamic_list_head.tpl"}
	{foreach from=$list->iterate() item="doc"}
		<tr>
			{if $list->hasColumn('type')}
				<td>{$doc.type}</td>
			{/if}
				<th>{if !$doc.number}{tag label="Brouillon"}{else}{$doc.number}{/if}</th>
				<td>{$doc.date_created|date_short}</td>
				<td>{$doc.date_expiry|date_short}</td>
				<td>{$doc.client_name}</td>
				<td class="money">{$doc.total|raw|money_currency_html}</td>
				<td>{tag label=$doc.status_label color=$doc.status_color}</td>
				<td class="actions">
					{linkbutton shape="menu" label="Détails" href="details.html?id=%d"|args:$doc.id}
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