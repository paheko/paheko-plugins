{include file="_head.tpl" title="Sessions de caisse"}

<p>
	{linkbutton href="session_open.php" shape="right" label="Ouvrir la caisse" class="main"}
	{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_ADMIN)}
		{linkbutton href="manage/" label="Gestion et statistiques" shape="settings"}
	{/if}
</p>

{include file="common/dynamic_list_head.tpl"}
		{foreach from=$list->iterate() item="pos_session"}
		<tr>
			<td class="num">
				{link href="session.php?id=%d"|args:$pos_session.id label=$pos_session.id}
			</td>
			<th>
				{$pos_session.opened|date}
				<small>({$pos_session.open_user})</small>
			</th>
			<td class="money">{$pos_session.open_amount|raw|money_currency}</td>
			<td>
				{if !$pos_session.closed}
					<strong>En cours</strong>
				{else}
					{if $pos_session.closed_same_day}
						à {$pos_session.closed|date_hour}
					{else}
						{$pos_session.closed|date}
					{/if}

					{if $pos_session.close_user != $pos_session.open_user}<small>({$pos_session.close_user})</small>{/if}
				{/if}
			</td>
			<td class="money">{$pos_session.close_amount|raw|money_currency}</td>
			<td class="money">
				{if $pos_session.error_amount}
					<span class="error">{$pos_session.error_amount|raw|money_currency}</span>
				{/if}
			</td>
			<td class="money">{$pos_session.total|raw|money_currency}</td>
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