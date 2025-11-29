{include file="_head.tpl" title="Sessions de caisse"}

<nav class="tabs">
	<aside>
		{exportmenu}
		{linkbutton href="debts.php" label="Ardoises" shape="history"}
	{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_ADMIN)}
		{linkbutton href="manage/" label="Gestion et statistiques" shape="settings"}
	{/if}
	</aside>

	{if $current_pos_session}
		{linkbutton href="tab.php?session=%d"|args:$current_pos_session shape="right" label="Reprendre la session" class="main"}
	{/if}
	{linkbutton href="session_open.php" shape="plus" label="Ouvrir une session de caisse" class="main" target="_dialog"}
</nav>

{include file="common/dynamic_list_head.tpl"}
		{foreach from=$list->iterate() item="pos_session"}
		<tr>
			{if $has_locations}
			<td>{$pos_session.location}</td>
			{/if}
			<td class="num">
				{link href="session.php?id=%d"|args:$pos_session.id label=$pos_session.id}
			</td>
			<td>
				<small>{$pos_session.open_user}</small>
				{if $pos_session.close_user != $pos_session.open_user}<br /><small>(&rarr; {$pos_session.close_user})</small>{/if}
			</td>
			<td>
				{$pos_session.opened|date_format:"%a %d %B %Y"}
			</td>
			<td>
				{$pos_session.opened|date_format:"%H:%M"}
			</td>
			<td>
				{if !$pos_session.closed}
					<strong>En cours</strong>
				{else}
					{if $pos_session.closed_same_day}
						&rarr; {$pos_session.closed|date_hour}
					{else}
						&rarr; <small>{$pos_session.closed|strftime:"%a %d/%m à %H:%M"}</small>
					{/if}
				{/if}
			</td>
			<td class="money">{$pos_session.open_amount|raw|money_currency}</td>
			<td class="money">{$pos_session.close_amount|raw|money_currency}</td>
			<td class="money">{$pos_session.total|raw|money_currency}</td>
			<td class="money">
				{if $pos_session.error_amount}
					<span class="error">{$pos_session.error_amount|raw|money_currency}</span>
				{/if}
			</td>
			<td class="num">{$pos_session.tabs_count}</td>
			<td class="actions">
				{if !$pos_session.closed}
				{linkbutton shape="right" label="Reprendre" href="tab.php?session=%d"|args:$pos_session.id}
				{linkbutton shape="lock" label="Clôturer" href="session_close.php?id=%s"|args:$pos_session.id}
				{/if}
				{linkbutton shape="menu" label="Résumé" href="session.php?id=%s"|args:$pos_session.id}
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>

{$list->getHTMLPagination()|raw}

{include file="_foot.tpl"}