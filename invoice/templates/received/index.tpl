{include file="_head.tpl" title="Factures reçues" current="plugin_invoice"}

{include file="../_nav.tpl" current="received"}

{if $list->count()}
	{include file="common/dynamic_list_head.tpl"}
	{foreach from=$list->iterate() item="doc"}
		<tr>
				<td>{$doc.type_label}</td>
				<td class="num">{$doc.number}</td>
				<td>{$doc.date|date_short}</td>
				<th>{$doc.label}</th>
				<td>{$doc.person_name}</td>
				<td class="money">{$doc.total|raw|money_currency_html:false}</td>
			{if $list->hasColumn('status')}
				<td>{tag label=$doc.status_label status=$doc.status_color}</td>
			{/if}
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