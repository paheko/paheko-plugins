{{#load where="$$.type = 'quote' OR $$.type = 'invoice'" limit=1}}
	{{:error message="La configuration de la numérotation ne peut être modifiée."}}
{{/load}}

{{:admin_header title="Configuration de la numérotation"}}

{{#form on="save"}}
	{{if $_POST.first_invoice_number|intval < 1}}
		{{:error message="Le numéro de la première facture ne peut être inférieur à 1."}}
	{{elseif $_POST.first_quote_number|intval < 1}}
		{{:error message="Le numéro du premier devis ne peut être inférieur à 1."}}
	{{/if}}

	{{:save key="config"
		first_invoice_number=$_POST.first_invoice_number|intval
		first_quote_number=$_POST.first_quote_number|intval
	}}
	{{:redirect self="./edit.html?type=%s"|args:$_GET.type}}
{{/form}}

{{:form_errors}}

<form method="post" action="">

<p class="help">Avant de commencer à créer des factures et devis, merci d'indiquer ici le numéro de la première facture et du premier devis, au cas où vous continuez une facturation existante.</p>

<fieldset>
	<legend>Configuration de la numérotation</legend>
	<dl>
		{{:input type="number" default=1 name="first_invoice_number" label="Numéro du premier devis" source=$module.config}}
		{{:input type="number" default=1 name="first_quote_number" label="Numéro de la première facture" source=$module.config}}
	</dl>
</fieldset>

<p class="alert block">
	Cette configuration ne pourra plus être modifiée une fois enregistrée !
</p>

<p class="submit">
	{{:button type="submit" name="save" label="Enregistrer" shape="right" class="main"}}
</p>

</form>

{{:admin_footer}}