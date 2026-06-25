{include file="_head.tpl" title=$title current="plugin_invoice"}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1">

<fieldset>
	<legend>Informations</legend>
	<dl>
		{input type="list" required=true name="client" label="Client" target="!p/invoice/clients/selector.php"}
		{input required=true name="label" type="text" label="Libellé" source=$invoice}
		{input required=true name="date" type="date" label="Date d'émission" source=$invoice default=$now}
		{if $invoice->isQuote()}
			{input required=true name="date_expiry" type="date" label="Date d'expiration" source=$invoice help="Après cette date le devis ne sera plus valide."}
		{else}
			{input required=true name="date_expiry" type="date" label="Date d'échéance" source=$invoice help="Après cette date la facture sera considérée en souffrance (délai de paiement dépassé)."
		{/if}
		{input required=false name="notes" type="textarea" cols=50 rows=5 label="Notes" source=$invoice help="Informations à faire figurer sur le document"}
	</dl>
	<details>
		<summary>Informations supplémentaires (Chorus Pro)</summary>
		<p class="help">Ces informations peuvent être nécessaires pour certains services de l'État français, pour la facturation électronique via Chorus Pro.</p>
		<dl>
			{input type="text" name="buyer_ref" label="Code du service exécutant" source=$invoice}
			{input type="text" name="contract_reference" label="Référence d'engagement" source=$invoice}
		</dl>
	</details>
</fieldset>

<p class="submit">
	{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
	{csrf_field key=$csrf_key}
</p>

</form>

{include file="_foot.tpl"}
