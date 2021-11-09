{include file="admin/_head.tpl" title="Commande n°%s — %s"|args:$order.id,$order.payer_name current="plugin_%s"|args:$plugin.id}

{include file="%s/templates/_menu.tpl"|args:$plugin_root current="home"}

<h2 class="ruler">Informations de la commande</h2>

<dl class="describe">
	<dt>Personne</dt>
	<dd>{$order.payer_name}</dd>
	<dt>Référence</dt>
	<dd>{$order.id}</dd>
	<dt>Montant total</dt>
	<dd>{$order.amount.total|money_currency|raw}</dd>
	<dt>Date</dt>
	<dd>{$order.date|date}</dd>
	<dt>Statut</dt>
	<dd>{if $order.status}Payée{else}Paiement incomplet{/if}</dd>
</dl>

<h2 class="ruler">Éléments de la commande</h2>

<table class="list">
	<thead>
		<tr>
			<td class="num">Réference</td>
			<td class="money">Montant</td>
			<td>Type</td>
			<td>Libellé</td>
			<td>Personne</td>
			<td>Détails</td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$order.items item="item"}
		<tr>
			<td class="num">{$item.id}</td>
			<td class="money">{$item.amount|money_currency|raw}</td>
			<td>{$item.type_name}</td>
			<td>{$item.name}</td>
			<th>{$item.user_name}</th>
			<td>
			{if $item.customFields}
				<dl class="describe">
				{foreach from=$item.customFields item="field"}
				<dt>{$field.name}</dt>
				<dd>{$field.answer}</dd>
				{/foreach}
				</dl>
			{/if}
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>

<h2 class="ruler">Paiements</h2>

<table class="list">
	<thead>
		<tr>
			<th>Réference</th>
			<td>Date</td>
			<td class="money">Montant</td>
			<td>Statut</td>
			<td>Versement</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$order.payments item="row"}
		<tr>
			<th><a href="payment.php?id={$row.id}">{$row.id}</a></th>
			<td>{$row.date|date}</td>
			<td class="money">{$row.amount|money_currency|raw}</td>
			<td>{$row.status}</td>
			<td>{if $row.transferred}Effectué{else}En attente{/if}</td>
			<td class="actions">
				{if $row.paymentReceiptUrl}
				{linkbutton href=$row.paymentReceiptUrl target="_blank" shape="print" label="Attestation de paiement"}
				{/if}
				{linkbutton href="payment.php?id=%s"|args:$row.id shape="help" label="Détails"}
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>

<h2 class="ruler">Personne ayant effectué le paiement</h2>

<dl class="describe">
	{foreach from=$order.payer_infos key="key" item="value"}
	<dt>{$key}</dt>
	<dd>
		{if $value instanceof \DateTime}
			{$value|date:'d/m/Y'}
		{else}
			{$value}
		{/if}
	</dd>
	{/foreach}
</dl>

{*
{if $found_user}
<p class="block confirm">
	Membre correspondant trouvé : <a href="{$admin_url}membres/fiche.php?id={$found_user.id}">{$found_user.identity}</a>
</p>
{else}
<form method="post" action="{$admin_url}membres/ajouter.php">
<p class="alert block">
	Aucun membre correspondant n'a été trouvé.<br />
	{foreach from=$mapped_user key="key" item="value"}
	<input type="hidden" name="{$key}" value="{$value}" />
	{/foreach}
	{button type="submit" shape="plus" label="Créer un membre avec ces informations"}
</p>
</form>
{/if}
*}

{include file="admin/_foot.tpl"}
