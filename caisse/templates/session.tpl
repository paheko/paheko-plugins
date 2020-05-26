{include file="admin/_head.tpl" current="plugin_%s"|args:$plugin.id}

{if !$pos_session.closed}
	<ul class="actions">
		<li><a href="session_close.php">Clôturer la caisse</a></li>
	</ul>
{/if}

<h3>{$title}</h3>

<h2 class="ruler">Règlements</h2>

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
			<td></td>
		</tr>
		{/foreach}
	</tbody>
</table>

<h2 class="ruler">Totaux, par moyen de paiement</h2>

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


<h2 class="ruler">Totaux, par catégorie</h2>

<table class="list">
	<thead>
		<tr>
			<td>Catégorie</td>
			<td>Montant</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$totals_categories item="cat"}
		<tr>
			<th>{$cat.cat_name}</th>
			<td>
				{$cat.total|raw|pos_money}
			</td>
			<td></td>
		</tr>
		{/foreach}
	</tbody>
</table>


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
					<td colspan="2">{$tab.total|raw|pos_money}</td>
				</tr>
			</tfoot>
		</table>
	</section>

{/foreach}

{include file="admin/_foot.tpl"}