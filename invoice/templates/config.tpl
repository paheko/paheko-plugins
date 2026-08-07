{include file="_head.tpl" title="Configuration" current="plugin_invoice"}

<p>
	{linkbutton shape="left" href="./" label="Retour"}
</p>

{if isset($_GET['ok'])}
	<p class="block confirm">Configuration enregistrée.</p>
{/if}

{form_errors}

<form method="post" action="">

<fieldset>
	<legend>Informations administratives</legend>
	<dl>
		{input type="text" name="vat_number" source=$plugin_config label="Numéro de TVA intra-communautaire (UE)" required=false}
		{if $config.country === 'FR'}
			{input type="select" name="exemption_code" source=$plugin_config label="Motif d'exemption de TVA par défaut" required=false options=$vat_exemption_codes default_empty="— Aucune (pas d'exemption de TVA) —"}
		{else}
			{input type="text" name="exemption_text" source=$plugin_config label="Motif d'exemption de TVA par défaut" required=false}
		{/if}
	</dl>
</fieldset>

<fieldset>
	<legend>Instructions de paiement de paiement</legend>
	<p>Ces informations figureront sur les factures émises, pour indiquer au client comment payer.</p>
	<dl>
		{input type="text" name="iban" source=$plugin_config label="Numéro IBAN" required=false}
		{input type="text" name="bic" source=$plugin_config label="Code BIC" required=false}
		{input type="textarea" name="payment_instructions" source=$plugin_config label="Autres instructions de paiement" required=false}
	</dl>
</fieldset>

<p class="submit">
	{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
	{csrf_field key=$csrf_key}
</p>

</form>

{include file="_foot.tpl"}
