{include file="_head.tpl" title=$title}

<nav class="tabs">
	<aside>
		{linkbutton shape="left" href="./" label="Caisse"}
	</aside>
	<ul>
		<li>{link href="debts.php" label="Ardoises en cours"}</li>
		<li class="current">{link href="debts_history.php" label="Historique des ardoises et remboursements"}</li>
	</ul>
</nav>

{if $list->count()}
	{include file="common/dynamic_list_head.tpl"}
			{foreach from=$list->iterate() item="row"}
			<tr>
				<td>
					{if $row.method}
						{tag color="darkred" label=$row.method}
					{elseif $row.type === 'debt'}
						{tag color="darkred" label="Ardoise"}
					{else}
						{tag color="darkgreen" label="Paiement"}
					{/if}
				</td>
				<th>{$row.date|date_short}</th>
				<td class="num">{link href="tab.php?id=%d"|args:$row.id label="#%d"|args:$row.id}</td>
				<td>{$row.name}</td>
				<td class="money">{$row.amount|money_currency_html|raw}</td>
				<td class="actions">
					{if $row.user_id}
						{linkbutton href="!users/details.php?id=%d"|args:$row.user_id label="Fiche membre" shape="user"}
					{/if}
					{linkbutton href="tab.php?id=%d"|args:$row.id label="Note" shape="money"}
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