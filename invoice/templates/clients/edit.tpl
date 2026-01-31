{{if $_GET.key}}
	{{#load key=$_GET.key type="client" assign="client"}}
	{{else}}
		{{:error message="Client invalide ou introuvable"}}
	{{/load}}
	{{:assign title="Modifier un client"}}
{{else}}
	{{:assign title="Ajouter un nouveau client"}}
	{{:assign var="client" key=""|uuid}}
{{/if}}

{{#form on="save"}}
	{{if !$_POST.name|trim}}
		{{:error message="Le nom ne peut rester vide."}}
	{{elseif !$_POST.address|trim}}
		{{:error message="L'adresse ne peut rester vide."}}
	{{elseif !$_POST.country}}
		{{:error message="Le pays ne peut rester vide."}}
	{{elseif $_POST.email|trim && !$_POST.email|check_email}}
		{{:error message="L'adresse e-mail du client semble être invalide."}}
	{{elseif $_POST.business_number && $_POST.country === 'FR' && !$_POST.business_number|check_siret_number}}
		{{:error message="Ce numéro SIRET est invalide."}}
	{{/if}}

	{{#load type="client" name=$_POST.name|trim}}
		{{:error message="Un client avec ce nom existe déjà"}}
	{{/load}}

	{{:save key=$client.key
		validate_schema="./client.schema.json"
		type="client"
		name=$_POST.name|trim
		address=$_POST.address|trim
		country=$_POST.country
		phone=$_POST.phone|trim|or:null
		email=$_POST.email|trim|or:null
		notes=$_POST.notes|trim|or:null
		archived=$_POST.archived|boolval|intval
		business_number=$_POST.business_number|trim|or:null
		vat_number=$_POST.vat_number|trim|or:null
	}}

	{{if $_GET.type === 'quote' || $_GET.type === 'invoice'}}
		{{:redirect reload="../edit.html?client=%s&type=%s"|args:$client.key:$_GET.type}}
	{{else}}
		{{:redirect reload="./details.html?key=%s"|args:$client.key}}
	{{/if}}
{{/form}}

{{:admin_header title=$title}}

{{:form_errors}}

<form method="post" action="{{$self_uri}}" data-focus="1">

<fieldset>
	<legend>Informations générales</legend>
	<dl>
		{{:input type="text" name="name" source=$client label="Nom" required=true}}
		{{:input type="textarea" cols="50" rows="3" name="address" source=$client label="Adresse" required=true}}
		{{:input type="country" name="country" source=$client required=true label="Pays" default=$config.country}}
		{{:input type="tel" name="phone" source=$client label="Numéro de téléphone" required=false}}
		{{:input type="email" name="email" source=$client label="Adresse e-mail" required=false help="Pourra être utilisée pour envoyer devis et factures."}}
		{{:input type="textarea" cols="50" rows="4" name="notes" source=$client label="Notes" required=false help="Ces notes ne seront pas affichées sur les devis et factures, elles sont uniquement destinées à un usage interne."}}
		{{:input type="checkbox" value=1 name="archived" source=$client label="Client archivé" help="Si cette case est cochée, il ne sera plus possible de créer des devis et factures pour ce client."}}
	</dl>
</fieldset>

<fieldset>
	<legend>Informations administratives</legend>
	<p class="help country-fr">Ces informations sont nécessaires pour l'établissement d'une facture électronique en France (Chorus Pro).</p>
	<dl class="country-fr">
		{{:input type="text" name="business_number" source=$client label="Numéro de SIRET" required=false}}
	</dl>
	<dl class="country-other">
		{{:input type="text" name="business_number" source=$client label="Numéro d'entreprise" required=false}}
	</dl>
	<dl>
		{{:input type="text" name="vat_number" source=$client label="Numéro de TVA intra-communautaire" required=false}}
	</dl>
</fieldset>

<p class="submit">
	{{:button type="submit" name="save" label="Enregistrer" shape="right" class="main"}}
</p>

</form>

<script type="text/javascript">
function selectCountry()
{
	var c = $('#f_country').value;
	g.toggle('.country-fr', c === 'FR');
	g.toggle('.country-other', c !== 'FR');
}

$('#f_country').onchange = selectCountry;
selectCountry();
</script>

{{:admin_footer}}