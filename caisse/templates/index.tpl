{include file="admin/_head.tpl" title="Sessions de caisse" current="plugin_%s"|args:$plugin.id}

{if !$current_pos_session}
<ul class="actions">
	<li><a href="session.php">Ouvrir la caisse</a></li>
</ul>
{/if}

<table class="list">
	<thead>
		<tr>
			<th>Caisse ouverte par</th>
			<td colspan="2">Ouverture</td>
			<td colspan="2">Clôture</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$pos_sessions item="pos_session"}
		<tr>
			<th>{$pos_session.open_user_name}</th>
			<td>{$pos_session.opened|format_sqlite_date_to_french}</td>
			<td>{$pos_session.open_amount|raw|pos_money}</td>
			<td>{$pos_session.closed|format_sqlite_date_to_french}</td>
			<td>{$pos_session.close_amount|raw|pos_money}</td>
			<td class="actions">
				<a href="session.php?id={$pos_session.id}">Détails</a>
				{if !$pos_session.closed}
				| <a href="session.php?id={$pos_session.id}">Clôturer</a>
				| <a href="tab.php">Reprendre</a>
				{/if}
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>

{include file="admin/_foot.tpl"}