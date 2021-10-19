{include file="admin/_head.tpl" title="Paiement "|args:$payment.reference current="plugin_%s"|args:$plugin.id}

{include file="%s/templates/_menu.tpl"|args:$plugin_root current="home"}

<h2 class="ruler">Informations du paiement</h2>

<dl class="describe">
	<dt>Référence</dt>
	<dd>{$payment.reference}</dd>
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

<h3>Informations brutes</h3>

<p><textarea readonly="readonly" cols="70" rows="10">{$payment_json}</textarea></p>

{include file="admin/_foot.tpl"}
