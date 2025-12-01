{include file="_head.tpl" title=$title}
<nav class="tabs">
{if $dialog}
	{linkbutton shape="plus" href="tab_add_credit.php?id=%d"|args:$id_tab label="Créditer le porte-monnaie" class="main"}
{else}
	<aside>
		{exportmenu}
		{linkbutton shape="left" href="./" label="Caisse"}
	</aside>
	<ul>
		<li>{link href="balances.php?type=%d"|args:$type label=$section_title}</li>
		<li class="current">{link href="balances_history.php?type=%d"|args:$type label="Historique"}</li>
	</ul>
{/if}
</nav>

{if $list->count()}
	{include file="common/dynamic_list_head.tpl"}
			{foreach from=$list->iterate() item="row"}
			<tr>
				<td>
					{if $row.type === 'credit'}
						{tag color="darkcyan" label="Crédit du solde"}
					{elseif $row.type === 'payment'}
						{tag color="darkorange" label="Paiement avec le solde"}
					{elseif $row.type === 'debt'}
						{tag color="darkred" label="Ardoise"}
					{elseif $row.type === 'payoff'}
						{tag color="darkgreen" label="Remboursement"}
					{/if}
				</td>
				<th>{$row.date|date_short}</th>
				<td class="num">{link href="tab.php?id=%d"|args:$row.id label="#%d"|args:$row.id target="_top"}</td>
				<td>{$row.name}</td>
				<td class="money">{$row.amount|money_currency_html:true:true|raw}</td>
				<td>{$row.method}</td>
				<td class="actions">
					{if $row.user_id}
						{linkbutton href="!users/details.php?id=%d"|args:$row.user_id label="Fiche membre" shape="user" target="_top"}
					{/if}
					{linkbutton href="tab.php?id=%d"|args:$row.id label="Note" shape="money" target="_top"}
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>

	{$list->getHTMLPagination()|raw}
{else}
	<p class="block alert">Il n'y a aucune ardoise dans l'historique.</p>
{/if}

{include file="_foot.tpl"}