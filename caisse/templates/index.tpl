{include file="admin/_head.tpl" title="Sessions de caisse" current="plugin_%s"|args:$plugin.id}

{if !$current_pos_session}
<ul class="actions">
	<li><a href="session.php">Ouvrir la caisse</a></li>
</ul>
{/if}

<table class="list">
	<thead>
		<tr>
			<td colspan="3">Ouverture</td>
			<td colspan="4">Clôture</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$pos_sessions item="pos_session"}
		<tr>
			<td>{$pos_session.opened|format_sqlite_date_to_french}</td>
			<th>{$pos_session.open_user_name}</th>
			<td>{$pos_session.open_amount|raw|pos_money}</td>
			<td>{if !$pos_session.closed}<strong>En cours</strong>{else}{$pos_session.closed|format_sqlite_date_to_french}{/if}</td>
			<th>{$pos_session.close_user_name}</th>
			<td>{$pos_session.close_amount|raw|pos_money}</td>
			<td>
				{if $pos_session.error_amount}
					<span class="error">Erreur de {$pos_session.error_amount|raw|pos_money}</span>
				{/if}
			</td>
			<td class="actions">
				{if !$pos_session.closed}
				<strong><a href="tab.php">Reprendre</a></strong>
				| <a href="session_close.php?id={$pos_session.id}">Clôturer</a>
				|
				{/if}
				<a href="session.php?id={$pos_session.id}">Résumé</a>
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>

{include file="admin/_foot.tpl"}