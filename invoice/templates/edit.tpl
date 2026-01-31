{{if $_GET.doc}}
	{{#load key=$_GET.doc assign="doc" where="$$.type = 'quote' OR $$.type = 'invoice'"}}
	{{else}}
		{{:error message="Ce document n'existe pas ou plus"}}
	{{/load}}
{{else}}
	{{if $_GET.type !== 'quote' && $_GET.type !== 'invoice'}}
		{{:error message="Type de document inconnu"}}
	{{/if}}

	{{:assign var="doc" status="draft" key=""|uuid type=$_GET.type number=null}}
	{{:assign var="doc.lines."}}
	{{:assign var="doc.lines."}}
{{/if}}

{{if $doc.status !== 'draft'}}
	{{:error message="Ce document n'est plus un brouillon et ne peut plus être modifié"}}
{{/if}}

{{if !$module.config.first_invoice_number}}
	{{:redirect self="./config_number.html?type="|cat:$doc.type}}
{{/if}}

{{#form on="save"}}
	{{if !$_POST.date|trim|parse_date}}
		{{:error message="Date d'émission invalide ou vide."}}
	{{elseif $_POST.date_expiry|trim|parse_date === false}}
		{{:error message="Date d'échéance invalide."}}
	{{elseif !$_POST.label|trim}}
		{{:error message="Le libellé ne peut rester vide."}}
	{{/if}}

	{{#load type="client" key=$_POST.client}}
	{{else}}
		{{:error message="Client invalide"}}
	{{/load}}

	{{:save key=$doc.key
		validate_schema="./doc.schema.json"
		type=$doc.type
		status="draft"
		number=$doc.number
		label=$_POST.label|trim
		date=$_POST.date|trim|parse_date
		date_expiry=$_POST.date_expiry|trim|parse_date|or:null
		client=$_POST.client
		header_text=$_POST.header_text|trim
	}}

	{{:redirect reload="./details.html?doc=%s"|args:$doc.key}}
{{/form}}

{{if !$doc.id}}
	{{if $doc.type === 'invoice'}}
		{{:assign title="Nouvelle facture"}}
	{{else}}
		{{:assign title="Nouveau devis"}}
	{{/if}}
{{else}}
	{{if $doc.type === 'invoice'}}
		{{:assign title="Modifier une facture"}}
	{{else}}
		{{:assign title="Modifier un devis"}}
	{{/if}}
{{/if}}

{{#load type="client" archived=0 order="$$.name COLLATE U_NOCASE ASC"}}
	{{:assign var="clients_options.%s"|args:$key value=$name}}
	{{:assign var="clients.%s"|args:$key country=$country business_number=$business_number vat_number=$vat_number}}
{{/load}}

{{if !$clients|count}}
	{{:redirect parent="./clients/?msg=NONE"}}
{{/if}}

{{:admin_header title=$title}}

{{:form_errors}}

<form method="post" action="{{$self_uri}}" data-focus="1">

<fieldset>
	<legend><label for="f_client">Client</label></legend>
	<dl>
		<dd>
			{{:input type="select" options=$clients_options name="client" source=$doc required=true default=$_GET.client}}
			{{:linkbutton shape="plus" label="Nouveau client" href="clients/edit.html?type=%s"|args:$doc.type}}
		</dd>
	</dl>
</fieldset>

<fieldset>
	<legend>Détails</legend>
	<dl>
		{{:input required=true name="label" type="text" label="Libellé" source=$doc}}
		{{:input required=true name="date" type="date" label="Date d'émission" source=$doc default=$now}}
		{{if $doc.type === 'invoice'}}
			{{:input required=false name="date_expiry" type="date" label="Date d'échéance" source=$doc help="Après cette date la facture sera considérée comme étant en souffrance."}}
			{{:input required=false name="header_text" type="textarea" cols=70 rows=5 label="Texte à faire figurer au début de la facture" source=$doc help="(MarkDown autorisé)"}}
		{{else}}
			{{:input required=false name="date_expiry" type="date" label="Date d'échéance" source=$doc help="Après cette date le devis ne sera plus valide."}}
			{{:input required=false name="header_text" type="textarea" cols=70 rows=5 label="Texte à faire figurer au début du devis" source=$doc help="(MarkDown autorisé)"}}
		{{/if}}
	</dl>
</fieldset>

{{if $doc.type === 'invoice'}}
	<fieldset class="chorus">
		<legend>Informations Chorus Pro</legend>
		<p class="help">Ces informations peuvent être nécessaires pour certains services de l'État français, pour la facturation électronique via Chorus Pro.</p>
		<dl>
			{{:input type="text" name="buyer_ref" label="Code du service exécutant" source=$doc}}
			{{:input type="text" name="issuer_assigned_id" label="Référence d'engagement" source=$doc}}
		</dl>
	</fieldset>

	<script type="text/javascript">
	var clients = {{$clients|json_encode}};
	function selectClient()
	{
		var c = $('#f_client').value;

		if (clients[c].country === 'FR' && (clients[c].business_number || clients[c].vat_number)) {
			g.toggle('.chorus', true);
		}
		else {
			g.toggle('.chorus', false);
		}
	}

	$('#f_client').onchange = selectClient;
	selectClient();
	</script>
{{/if}}

<p class="submit">
	{{:button type="submit" name="save" label="Enregistrer" shape="right" class="main"}}
</p>

</form>

{{:admin_footer}}