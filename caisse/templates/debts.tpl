{include file="_head.tpl" title="Ardoises en cours"}

<nav class="tabs">
	<aside>
		{linkbutton shape="left" href="./" label="Caisse"}
	</aside>
	<ul>
		<li class="current">{link href="debts.php" label="Ardoises en cours"}</li>
		<li>{link href="debts_history.php" label="Historique des ardoises et remboursements"}</li>
	</ul>
</nav>

{if $list->count()}
	{include file="common/dynamic_list_head.tpl"}
			{foreach from=$list->iterate() item="row"}
			<tr>
				<th>{$row.date|date_short}</th>
				<td>{if !$row.name}<em>Anonyme</em>{else}{$row.name}{/if}</td>
				<td class="money">{$row.amount|money_currency_html|raw}</td>
				<td class="actions">
					{if $row.user_id}
						{linkbutton href="!users/details.php?id=%d"|args:$row.user_id label="Fiche membre" shape="user"}
						{linkbutton href="debts_history.php?user=%d"|args:$row.user_id label="Historique" shape="history"}
					{/if}
					{linkbutton href="tab.php?payoff_user=%d&payoff_amount=%d&payoff_account=%s"|args:$row.user_id:$row.amount:$row.account label="Rembourser" shape="right"}
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
	{$list->getHTMLPagination()|raw}
{else}
	<p class="block alert">Il n'y a aucune ardoise à régler.</p>
{/if}

{include file="_foot.tpl"}