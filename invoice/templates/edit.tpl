{include file="_head.tpl" title=$title current="plugin_invoice"}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1">

<fieldset>
	<legend>Informations</legend>
	<dl>
		{input type="list" required=true name="client" label="Client" target="!p/invoice/clients/selector.php" default=$invoice->getClientSelectorValue()}
		{input required=true name="label" type="text" label="Objet" source=$invoice}
		{input required=true name="date_created" type="date" label="Date d'émission" source=$invoice default=$now}
		{if $invoice->isQuote()}
			{input required=true name="date_expiry" type="date" label="Date d'expiration" source=$invoice help="Après cette date le devis ne sera plus valide."}
		{else}
			{input required=true name="date_expiry" type="date" label="Date d'échéance" source=$invoice help="Après cette date la facture sera considérée en souffrance (délai de paiement dépassé)."}
			{if $config.country === 'FR'}
				{input required=true type="select" name="operation_type" label="Nature de la facture" source=$invoice options=$invoice::OPERATION_TYPES default_empty="— Sélectionner —"}
			{/if}
			{if $config.country === 'FR'}
				{input type="select" name="vat_exemption_code" required=false label="Raison d'exemption de TVA" source=$invoice options=$invoice->getVATExemptionOptions() default=$plugin_config.exemption_code}
			{else}
				{input type="text" name="vat_exemption_text" required=false label="Raison d'exemption de TVA" source=$invoice  default=$plugin_config.exemption_text}
			{/if}
			<dd class="help">Si des lignes de la factures sont exemptées de TVA, indiquer ici la raison de l'exemption.</dd>
		{/if}
		{input required=false name="notes" type="textarea" cols=50 rows=5 label="Notes supplémentaires" source=$invoice help="Ces informations figureront sur le document"}
	</dl>
	{if !$invoice->isQuote()}
	<details>
		<summary>Informations supplémentaires (Chorus Pro)</summary>
		<p class="help">Ces informations peuvent être nécessaires pour certains services de l'État français, pour la facturation électronique via Chorus Pro.</p>
		<dl>
			{input type="text" name="buyer_ref" label="Code du service exécutant" source=$invoice}
			{input type="text" name="contract_reference" label="Référence d'engagement" source=$invoice}
		</dl>
	</details>
	{/if}
</fieldset>

<p class="submit">
	{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
	{csrf_field key=$csrf_key}
</p>

</form>

{include file="_foot.tpl"}
