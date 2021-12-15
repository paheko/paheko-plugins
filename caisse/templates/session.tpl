{include file="admin/_head.tpl" current="plugin_%s"|args:$plugin.id}

{if !$pos_session.closed}
	<nav class="tabs">
		{linkbutton href="tab.php" label="Retour à l'encaissement" shape="left"}
		{linkbutton href="session_close.php?id=%d"|args:$pos_session.id label="Clôturer la caisse" shape="delete"}
	</nav>
{/if}

<h1>{$title}</h1>

{if $pos_session.error_amount}
	<p class="error block">Erreur de {$pos_session.error_amount|raw|money_currency}</span>
{/if}

<p class="details">
	Ouverture&nbsp;: {$pos_session.opened|date}
	— par {$names.open_user_name}
	— Caisse = {$pos_session.open_amount|raw|money_currency}
</p>
<p class="details">
	Fermeture&nbsp;:
	{if !$pos_session.closed}<strong>En cours</strong>
	{else}{$pos_session.closed|date}
		— par {$names.close_user_name}
		— Caisse = {$pos_session.close_amount|raw|money_currency}
		{if !$pos_session.error_amount}
			— pas d'erreur
		{/if}
	{/if}
</p>

{if count($missing_users_tabs)}
<div class="noprint">
	<h2 class="ruler">Membres non inscrits</h2>

	<table class="list">
		<thead>
			<tr>
				<td>Note</td>
				<td>Nom</td>
				<td></td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$missing_users_tabs item="tab"}
			<tr>
				<th>{$tab.id}</th>
				<td>
					{$tab.name}
				</td>
				<td class="actions">
					<form method="post" action="{$admin_url}membres/ajouter.php">
						<input type="hidden" name="{$config.champ_identite}" value="{$tab.name}" />
						<input type="submit" value="Inscrire ce membre" />
					</form>
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
</div>
{/if}

<h2 class="ruler">Recettes par catégorie</h2>

<table class="list">
	<thead>
		<tr>
			<td>Catégorie</td>
			<td>Montant</td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$totals_categories item="cat"}
		<tr>
			<th>{$cat.category_name}</th>
			<td>
				{$cat.total|raw|money_currency}
			</td>
		</tr>
		{/foreach}
	</tbody>
	<tfoot>
		<tr>
			<th>Total</th>
			<td>{$total|raw|money_currency}</td>
		</tr>
	</tfoot>
</table>

<h2 class="ruler">Totaux des règlements, par moyen de paiement</h2>

<table class="list">
	<thead>
		<tr>
			<td>Moyen</td>
			<td>Montant</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$payments_totals item="payment"}
		<tr>
			<th>{$payment.method_name}</th>
			<td>
				{$payment.total|raw|money_currency}
			</td>
			<td></td>
		</tr>
		{/foreach}
	</tbody>
</table>


<h2 class="ruler">Liste des règlements</h2>

<table class="list">
	<thead>
		<tr>
			<td>Note n°</td>
			<th>Date</th>
			<td>Moyen</td>
			<td>Montant</td>
			<td>Référence</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$payments item="payment"}
		<tr>
			<td>{$payment.tab}</td>
			<th>
				{$payment.date|date_format:"%H:%M"}
			</th>
			<td>{$payment.method_name}</td>
			<td>
				{$payment.amount|raw|money_currency}
			</td>
			<td>{$payment.reference}</td>
			<td class="actions">
				{linkbutton shape="menu" label="Détails" href="tab.php?id=%d"|args:$payment.tab class="noprint"}
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>

<div class="noprint">

<h2 class="ruler">Notes</h2>

<table class="list">
	<thead>
		<tr>
			<td>N°</td>
			<th>Note</th>
			<td>Total</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$tabs item="tab"}
		<tr>
			<td>{$tab.id}</td>
			<th>
				{$tab.opened|date_format:"%H:%M"}
				{if $tab.closed}
				&rarr; {$tab.closed|date_format:"%H:%M"}
				{/if}
				— {$tab.name}
			</th>
			<td>
				{$tab.total|raw|money_currency}
			</td>
			<td class="actions">
				{linkbutton shape="menu" label="Détails" href="tab.php?id=%d"|args:$tab.id class="noprint"}
			</td>
		</tr>
		{/foreach}
	</tbody>
	<tfoot>
		<tr>
			<td></td>
			<th>Total</th>
			<td>{$total|raw|money_currency}</td>
			<td></td>
		</tr>
	</tfoot>
</table>

{foreach from=$tabs item="tab"}

	<h2 class="ruler">Note n°{$tab.id}&nbsp;:
		{$tab.opened|date_format:"%H:%M"}
		{if $tab.closed}
		&rarr; {$tab.closed|date_format:"%H:%M"}
		{/if}
		— {$tab.name}
	</h2>

	<section class="items">
		<table class="list">
			<thead>
				<th></th>
				<td>Qté</td>
				<td>Prix</td>
				<td>Total</td>
			</thead>
			<tbody>
			{foreach from=$tab.items item="item"}
			<tr>
				<th><small class="cat">{$item.category_name}</small> {$item.name}</th>
				<td>{$item.qty}</td>
				<td>{$item.price|raw|money_currency}</td>
				<td>{$item.total|raw|money_currency}</td>
			</tr>
			{/foreach}
			</tbody>
			<tfoot>
				<tr>
					<th>Total</th>
					<td></td>
					<td></td>
					<td>{$tab.total|raw|money_currency}</td>
				</tr>
			</tfoot>
		</table>
	</section>

{/foreach}

</div>

{include file="admin/_foot.tpl"}