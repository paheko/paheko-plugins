{include file="admin/_head.tpl" title="Sessions de caisse" current="plugin_%s"|args:$plugin.id}

{if !$current_pos_session || $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_ADMIN)}
<nav class="tabs">
	<ul>
		{if !$current_pos_session}
		<li><a href="session.php">Ouvrir la caisse</a></li>
		{/if}
		{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_ADMIN)}
		<li><a href="export.php">Export compta CSV</a></li>
		<li><a href="products.php">Gestion des produits</a></li>
		<li><a href="products_print.php">Impression produits et tarifs</a></li>
		{/if}
	</ul>
</nav>
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
			<td>{$pos_session.opened|date}</td>
			<th>{$pos_session.open_user_name}</th>
			<td>{$pos_session.open_amount|raw|pos_money}</td>
			<td>{if !$pos_session.closed}<strong>En cours</strong>{else}{$pos_session.closed|date}{/if}</td>
			<th>{$pos_session.close_user_name}</th>
			<td>{$pos_session.close_amount|raw|pos_money}</td>
			<td>
				{if $pos_session.error_amount}
					<span class="error">Erreur de {$pos_session.error_amount|raw|pos_money}</span>
				{/if}
			</td>
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

{include file="admin/_foot.tpl"}