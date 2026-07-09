{include file="_head.tpl" title=$title current="plugin_invoice"}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1">

{if $_GET.msg === 'CREATE'}
	<p class="alert block">Merci de créer un client pour pouvoir créer une facture.</p>
{/if}

<fieldset>
	<legend>Informations générales</legend>
	<dl>
		{input type="text" name="name" source=$client label="Nom" required=true}
		{input type="country" name="country" source=$client required=true label="Pays" default=$config.country}
		{input type="text" name="post_code" source=$client label="Code postal" required=true}
		{input type="text" name="city" source=$client label="Ville" required=true}
		{input type="textarea" cols="50" rows="3" name="address" source=$client label="Adresse" required=true}
		{input type="tel" name="phone" source=$client label="Numéro de téléphone" required=false}
		{input type="email" name="email" source=$client label="Adresse e-mail" required=false help="Pourra être utilisée pour envoyer devis et factures."}
		{input type="textarea" cols="50" rows="4" name="notes" source=$client label="Notes" required=false help="Ces notes ne seront pas affichées sur les devis et factures, elles sont uniquement destinées à un usage interne."}
		{input type="checkbox" value=1 name="archived" source=$client label="Client archivé" help="Si cette case est cochée, il ne sera plus possible de créer des devis et factures pour ce client."}
	</dl>
</fieldset>

<fieldset class="country-fr">
	<legend>Informations administratives</legend>
	<?php $enabled = !empty($client->business_number); ?>
	<dl>
		{input type="radio-btn" prefix_label="Facturation électronique" prefix_required=true name="e_invoicing" value=1 label="Activer la facturation électronique" help="Pour les entreprises, auto-entrepreneurs, etc." required=true default=$enabled}
		{input type="radio-btn" name="e_invoicing" value=0 label="Sans facturation électronique" help="Particuliers, associations non assujetties à la TVA, syndic non professionnel, etc." default=$enabled}
	</dl>
	<dl class="e_invoicing_1">
		{input type="text" name="fr_business_number" default=$client.business_number label="Numéro SIREN" required=true maxlength=9 pattern="\d+" minlength=9}
		{input type="text" name="fr_vat_number" default=$client.vat_number label="Numéro de TVA intra-communautaire" required=false}
	</dl>
</fieldset>

<fieldset class="country-other">
	<legend>Informations administratives</legend>
	<dl class="e_invoicing_1">
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

	g.toggle('.country-other', c !== 'FR');
	g.toggle('.country-fr', c === 'FR');
}

function selectEInvoicing()
{
	var e = $('#f_e_invoicing_1');
	g.toggle('.e_invoicing_1', e.checked);
}

$('#f_country').onchange = selectCountry;
$('#f_e_invoicing_0').onchange = selectEInvoicing;
$('#f_e_invoicing_1').onchange = selectEInvoicing;
selectCountry();
selectEInvoicing();
</script>
{/literal}

{include file="_foot.tpl"}