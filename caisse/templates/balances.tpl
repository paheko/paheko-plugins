{include file="_head.tpl" title=$title}

<nav class="tabs">
	<aside>
		{exportmenu}
		{linkbutton shape="left" href="./" label="Caisse"}
	</aside>
	<ul>
		<li class="current">{link href="balances.php?type=%d"|args:$type label=$title}</li>
		<li>{link href="balances_history.php?type=%d"|args:$type  label="Historique"}</li>
	</ul>
</nav>

{if $list->count()}
	{include file="common/dynamic_list_head.tpl"}
			{foreach from=$list->iterate() item="row"}
			<tr>
				<th>{$row.date|date_short}</th>
				<td>{$row.name}</td>
				<td class="money">{$row.amount|money_currency_html:false|raw}</td>
				<td class="actions">
					{if $row.user_id}
						{linkbutton href="!users/details.php?id=%d"|args:$row.user_id label="Fiche membre" shape="user"}
						{linkbutton href="balances.php?type=%d&id_user="|args:$type:$row.user_id label="Historique" shape="history"}
					{/if}
					{if $is_debt}
						<?php $name = rawurlencode($row->name); ?>
						{linkbutton href="tab.php?id_user=%d&payoff=%d&id_method=%d&name=%s"|args:$row.user_id:$row.amount:$row.id_method:$name label="Rembourser" shape="right"}
					{/if}
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
	{$list->getHTMLPagination()|raw}
{else}
	<p class="block alert">{if $type === 2}Il n'y a aucune ardoise à régler.{else}Il n'y a aucun porte-monnaie de membre crédité.{/if}</p>
{/if}

{include file="_foot.tpl"}