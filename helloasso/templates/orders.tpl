{include file="_head.tpl" title="%s — %s"|args:$f.org_name:$f.name}

{include file="./_menu.tpl" current="home" current_sub="orders" show_export=true}

{if !$list->count()}
	<p class="alert block">Il n'y a aucune commande pour cette campagne.</p>
{else}
	{include file="common/dynamic_list_head.tpl"}

		{foreach from=$list->iterate() item="row"}

			<tr>
				<th class="num"><a href="order.php?id={$row.id}">{$row.id}</a></th>
				<td>{$row.date|date}</td>
				<td class="money">{$row.amount|money_currency|raw}</td>
				<td>{$row.person}</td>
				<td>{if $row.status}Payé{else}Paiement incomplet{/if}</td>
				<td>{if $row.id_user}{linkbutton shape="user" label="Fiche membre" href="!users/details.php?id=%d"|args:$row.id_user}{/if}</td>
				<td>{if $row.id_transaction}{link class="num" label="#%d"|args:$row.id_transaction href="!acc/transactions/details.php?id=%d"|args:$row.id_transaction}{/if}</td>
				{if $list->hasColumn('has_all_users')}
				<td>{if $row.has_all_users}{tag color="darkgreen" label="OK"}{else}{tag color="darkred" label="Manquantes"}{/if}</td>
				{/if}
				<td class="actions">
					{linkbutton href="order.php?id=%s"|args:$row.id shape="help" label="Détails"}
				</td>
			</tr>

		{/foreach}

		</tbody>
	</table>

	{$list->getHTMLPagination()|raw}
{/if}

{include file="_foot.tpl"}
