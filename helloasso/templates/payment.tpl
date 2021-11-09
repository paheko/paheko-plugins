{include file="admin/_head.tpl" title="Paiement "|args:$payment.reference current="plugin_%s"|args:$plugin.id}

{include file="%s/templates/_menu.tpl"|args:$plugin_root current="home"}

<h2 class="ruler">Informations du paiement</h2>

<dl class="describe">
	<dt>Référence</dt>
	<dd>{$payment.id}</dd>
	<dt>Commande</dt>
	<dd><a href="order.php?id={$payment.order_id}">{$payment.order_id}</a></dd>
	<dt>Montant</dt>
	<dd>{$payment.amount|money_currency|raw}</dd>
	<dt>Date</dt>
	<dd>{$payment.date|date}</dd>
	<dt>Statut</dt>
	<dd>{$payment.status}</dd>
	<dd>{linkbutton href=$payment.paymentReceiptUrl target="_blank" shape="print" label="Attestation de paiement"}</dd>
</dl>

<h2 class="ruler">Personne ayant effectué le paiement</h2>

<dl class="describe">
	{foreach from=$payment.payer_infos key="key" item="value"}
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

{include file="admin/_foot.tpl"}
