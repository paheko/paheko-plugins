{{:admin_header title="Configuration des devis et factures"}}

{{#form on="save"}}
	{{if $_POST.business_number && $config.country === 'FR' && !$_POST.business_number|check_siret_number}}
		{{:error message="Ce numéro SIRET est invalide."}}
	{{/if}}

	{{:save key="config"
		validate_schema="./config.schema.json"
		invoice_text=$_POST.invoice_text|or:null
		quote_text=$_POST.quote_text|or:null
		business_number=$_POST.business_number|or:null
		vat_number=$_POST.vat_number|or:null
	}}
	{{:redirect reload="./"}}
{{/form}}

{{if $_GET.ok}}
	<p class="block confirm">Configuration enregistrée.</p>
{{/if}}

{{:form_errors}}

<form method="post" action="">

<fieldset>
	<legend>Configuration</legend>
	<dl>
		{{:input type="textarea" cols="70" rows="5" name="invoice_text" required=false source=$module.config label="Texte à afficher en bas de chaque facture" help="Syntaxe MarkDown acceptée"}}
		{{:input type="textarea" cols="70" rows="5" name="quote_text" required=false source=$module.config label="Texte à afficher en bas de chaque facture" help="Syntaxe MarkDown acceptée"}}
	</dl>
</fieldset>

<fieldset>
	<legend>Informations administratives</legend>
	<p class="help">Ces informations sont nécessaires pour l'établissement d'une facture électronique en France (Chorus Pro).</p>
	<dl>
	{{if $config.country === 'FR'}}
		{{:input type="text" name="business_number" source=$module.config label="Numéro de SIRET" required=false}}
	{{else}}
		{{:input type="text" name="business_number" source=$module.config label="Numéro d'entreprise" required=false}}
	{{/if}}
		{{:input type="text" name="vat_number" source=$module.config label="Numéro de TVA intra-communautaire" required=false}}
	</dl>
</fieldset>

<p class="submit">
	{{:button type="submit" name="save" label="Enregistrer" shape="right" class="main"}}
</p>

</form>

{{:admin_footer}}