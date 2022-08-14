{include file="_head.tpl" title="Sessions de caisse" current="plugin_%s"|args:$plugin.id}

{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_ADMIN)}
<p>{linkbutton href="manage/" label="Gestion et statistiques" shape="settings"}</p>
{/if}

{if !$current_pos_session}
<p>{linkbutton href="session.php" shape="right" label="Ouvrir la caisse" class="main"}</p>
{/if}

{include file="common/dynamic_list_head.tpl"}
		{foreach from=$list->iterate() item="pos_session"}
		<tr>
			<td class="num">
				{link href="session.php?id=%d"|args:$pos_session.id label=$pos_session.id}
			</td>
			<th>
				{$pos_session.opened|date}
				<small>({$pos_session.open_user_name})</small>
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

					{if $pos_session.close_user_name != $pos_session.open_user_name}<small>({$pos_session.close_user_name})</small>{/if}
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
				{linkbutton shape="right" label="Reprendre" href="tab.php"}
				{linkbutton shape="lock" label="Clôturer" href="session_close.php?id=%s"|args:$pos_session.id}
				{/if}
				{linkbutton shape="menu" label="Résumé" href="session.php?id=%s"|args:$pos_session.id}
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>

{pagination url=$list->paginationURL() page=$list.page bypage=$list.per_page total=$list->count()}

{include file="_foot.tpl"}