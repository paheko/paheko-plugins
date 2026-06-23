{include file="_head.tpl" title=$title current="plugin_invoice"}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1">

<fieldset>
	<legend>Informations générales</legend>
	<dl>
		{input type="text" name="name" source=$client label="Nom" required=true}
		{input type="country" name="country" source=$client required=true label="Pays" default=$config.country}
		{input type="text" name="post_code" source=$client label="Code postal" required=false}
		{input type="text" name="city" source=$client label="Ville" required=false}
		{input type="textarea" cols="50" rows="3" name="address" source=$client label="Adresse" required=false}
		{input type="tel" name="phone" source=$client label="Numéro de téléphone" required=false}
		{input type="email" name="email" source=$client label="Adresse e-mail" required=false help="Pourra être utilisée pour envoyer devis et factures."}
		{input type="textarea" cols="50" rows="4" name="notes" source=$client label="Notes" required=false help="Ces notes ne seront pas affichées sur les devis et factures, elles sont uniquement destinées à un usage interne."}
		{input type="checkbox" value=1 name="archived" source=$client label="Client archivé" help="Si cette case est cochée, il ne sera plus possible de créer des devis et factures pour ce client."}
	</dl>
</fieldset>

<fieldset>
	<legend>Informations administratives</legend>
	<p class="help">Ces informations sont nécessaires pour l'établissement d'une facture électronique.</p>
	<dl>
		{input type="text" name="business_number" source=$client label="Numéro d'entreprise" required=false}
		{input type="text" name="vat_number" source=$client label="Numéro de TVA intra-communautaire" required=false}
	</dl>
</fieldset>

<p class="submit">
	{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
	{csrf_field key=$csrf_key}
</p>

</form>

{literal}
<script type="text/javascript">
function selectCountry()
{
	var c = $('#f_country').value;
	var input = $('#f_business_number');
	var label = input.parentNode.querySelector('label');
	input.maxLength = null;

	if (c === 'FR') {
		input.maxLength = 9;
		label.innerText = 'Numéro SIREN';
	}
	else if (c === 'BE') {
		label.innerText = 'Numéro BCE';
	}
	else {
		label.innerText = 'Numéro d\'entreprise';
	}
}

$('#f_country').onchange = selectCountry;
selectCountry();
</script>
{/literal}

{include file="_foot.tpl"}