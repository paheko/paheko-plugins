{{#load key=$_GET.key type="client" assign="client"}}
{{else}}
	{{:error message="Client invalide ou introuvable"}}
{{/load}}

{{:include file="../_defines.tpl"}}

{{:admin_header title="Client : %s"|args:$client.name current="acc"}}

{{:include file="../_nav.html" current="clients" client=$client}}

<dl class="describe">
	<dt>Nom</dt>
	<dd><h3>{{$client.name}}</h3></dd>
	<dt>Adresse</dt>
	<dd>{{$client.address|escape|nl2br}}</dd>
	<dt>Pays</dt>
	<dd>{{$client.country|get_country_name}}</dd>
	<dt>Téléphone</dt>
	{{if $client.phone}}
		{{:assign tel=$client.phone|urlencode}}
		<dd>{{:link href="tel:%s"|args:$tel label=$client.phone|format_phone_number}}</dd>
	{{else}}
		<dd><em>— Non renseigné —</em></dd>
	{{/if}}
	<dt>Adresse e-mail</dt>
	{{if $client.email}}
		{{:assign email=$client.email|urlencode}}
		<dd>{{:link href="mailto:%s"|args:$email label=$client.email}}</dd>
	{{else}}
		<dd><em>— Non renseignée —</em></dd>
	{{/if}}
	<dt>Notes</dt>
	{{if !$client.notes}}
		<dd><em>— Non renseigné —</em></dd>
	{{else}}
		<dd>{{$client.notes|escape|nl2br}}</dd>
	{{/if}}
	<dt>Archivé</dt>
	<dd>{{if $client.archived}}{{:icon shape="check"}} Oui{{else}}Non{{/if}}</dd>
	<dt>{{if $client.country === 'FR'}}Numéro de SIRET{{else}}Numéro d'entreprise{{/if}}</dt>
	{{if !$client.business_number}}
		<dd><em>— Non renseigné —</em></dd>
	{{else}}
		<dd>{{$client.business_number}}</dd>
	{{/if}}
	<dt>Numéro de TVA</dt>
	{{if !$client.vat_number}}
		<dd><em>— Non renseigné —</em></dd>
	{{else}}
		<dd>{{$client.vat_number}}</dd>
	{{/if}}
</dl>

<h2 class="ruler">Devis et factures pour ce client</h2>

{{#list select="$$.ref AS 'Numéro'; $$.date AS 'Date'; $$.date_expiry AS 'Échéance'; $$.total AS 'Montant'; NULL AS 'Statut'" where="$$.type = 'invoice' OR $$.type = 'quote'"}}
		<tr>
			{{if $first_column}}
			<td>{{$col1}}</td>
			{{/if}}
			<th>{{if !$number}}<em>(Brouillon)</em>{{else}}{{$number}}{{/if}}</th>
			<td>{{$date|date_short}}</td>
			<td>{{$date_expiry|date_short}}</td>
			<td class="money">{{$total|raw|money_currency}}</td>
			<td>
				{{:call function="display_status_label"}}
			</td>
			<td class="actions">
				{{:linkbutton shape="menu" label="Détails" href="../details.html?doc=%s"|args:$key}}
			</td>
		</tr>
{{else}}
	<p class="alert block">Aucun document ici.</p>
{{/list}}

{{:admin_footer}}
