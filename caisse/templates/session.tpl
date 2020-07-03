{include file="admin/_head.tpl" current="plugin_%s"|args:$plugin.id}

{if !$pos_session.closed}
	<ul class="actions">
		<li><a href="session_close.php?id={$pos_session.id}">Clôturer la caisse</a></li>
	</ul>
{/if}

<h1>{$title}</h1>

{if $pos_session.error_amount}
	<p class="error">Erreur de {$pos_session.error_amount|raw|pos_money}</span>
{/if}

<p class="details">
	Ouverture&nbsp;: {$pos_session.opened|format_sqlite_date_to_french}
	— par {$names.open_user_name}
	— Caisse = {$pos_session.open_amount|raw|pos_money}
</p>
<p class="details">
	Fermeture&nbsp;:
	{if !$pos_session.closed}<strong>En cours</strong>{else}{$pos_session.closed|format_sqlite_date_to_french}{/if}
	— par {$names.close_user_name}
	— Caisse = {$pos_session.close_amount|raw|pos_money}
	{if !$pos_session.error_amount}
		— pas d'erreur
	{/if}
</p>

{if count($missing_users_tabs)}
<div class="noprint">
	<h2 class="ruler">Membres à inscrire</h2>

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
				{$cat.total|raw|pos_money}
			</td>
		</tr>
		{/foreach}
	</tbody>
	<tfoot>
		<tr>
			<th>Total</th>
			<td>{$total|raw|pos_money}</td>
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
				{$payment.total|raw|pos_money}
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
				{$payment.amount|raw|pos_money}
			</td>
			<td>{$payment.reference}</td>
			<td class="actions"><span class="noprint"><a href="tab.php?id={$payment.tab}" class="icn" title="Détails">𝍢</a></span></td>
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
				{$tab.total|raw|pos_money}
			</td>
			<td class="actions"><span class="noprint"><a href="tab.php?id={$tab.id}" class="icn" title="Détails">𝍢</a></span></td>
		</tr>
		{/foreach}
	</tbody>
	<tfoot>
		<tr>
			<td></td>
			<th>Total</th>
			<td>{$total|raw|pos_money}</td>
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
				<td>{$item.price|raw|pos_money}</td>
				<td>{$item.total|raw|pos_money}</td>
			</tr>
			{/foreach}
			</tbody>
			<tfoot>
				<tr>
					<th>Total</th>
					<td></td>
					<td></td>
					<td>{$tab.total|raw|pos_money}</td>
				</tr>
			</tfoot>
		</table>
	</section>

{/foreach}

</div>

{include file="admin/_foot.tpl"}