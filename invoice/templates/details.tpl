{{#load assign="doc" key=$_GET.doc where="$$.type = 'invoice' OR $$.type = 'quote'"}}
{{else}}
	{{:error message="Ce document n'existe pas"}}
{{/load}}

{{#load assign="client" type="client" key=$doc.client}}
{{/load}}

{{:include file="./_defines.tpl"}}

{{if $doc.status === 'draft'}}
	{{#form on="validate"}}
		{{* Create invoice/quote number *}}
		{{#load type=$doc.type count=true where="$$.status != 'draft'"}}
			{{:assign count="%d+1"|math:$count}}
		{{else}}
			{{if $doc.type === 'quote'}}
				{{:assign count=$module.config.first_quote_number}}
			{{else}}
				{{:assign count=$module.config.first_invoice_number}}
			{{/if}}
		{{/load}}

		{{:assign year=$now|date:'Y'}}
		{{:assign number="%d-%d"|args:$year:$count}}

		{{:save
			key=$doc.key
			status="waiting"
			number=$number
			validate_schema="./doc.schema.json"
		}}

		{{:redirect reload="details.html?doc=%s"|args:$doc.key}}
	{{/form}}

	{{#form on="delete_line"}}
		{{:delete type="line" document=$doc.key key=$_POST.delete_line}}
		{{:redirect reload="details.html?doc=%s"|args:$doc.key}}
	{{/form}}

{{elseif $doc.status !== 'cancelled'}}
	{{#form on="cancel"}}
		{{:save key=$doc.key validate_schema="./doc.schema.json" status="cancelled"}}
		{{:redirect reload="./details.html?doc=%s"|args:$doc.key}}
	{{/form}}
{{/if}}

{{if $doc.type === 'invoice'}}
	{{:assign type_label="Facture"}}
{{else}}
	{{:assign type_label="Devis"}}
{{/if}}

{{:admin_header title="%s %s — %s"|args:$type_label:$doc.number:$doc.label}}

<form method="post" action="">

<nav class="tabs">
	<aside>
		{{:linkbutton shape="plus" label="Dupliquer" href="./duplicate.html?doc=%s"|args:$doc.key}}
		{{if $doc.status === 'draft'}}
			{{:linkbutton shape="delete" label="Supprimer" href="./delete.html?doc=%s"|args:$doc.key target="_dialog"}}
			{{:linkbutton shape="edit" label="Modifier" href="./edit.html?doc=%s"|args:$doc.key target="_dialog"}}
		{{/if}}
	</aside>
	{{:linkbutton shape="left" label="Retour à la liste" href="./"}}
</nav>

</form>

{{:form_errors}}

{{#load select="SUM($$.amount) AS total" where="$$.type = 'line' AND $$.document = :document" :document=$doc.key}}
	{{:assign total=$total}}
{{/load}}

<form method="post" action="">

{{if $doc.status === 'draft' && $total}}
	<div class="alert block">
		<h3>Statut&nbsp;: brouillon</h3>
		<p class="submit">{{:button shape="check" name="validate" label="Valider" type="submit" class="main"}}</p>
		<p>En cliquant sur ce bouton, le document sera verrouillé, il ne pourra plus être modifié, ni supprimé.</p>
	</div>
{{elseif $doc.status === 'waiting' && $doc.type === 'quote'}}
	<div class="alert block">
		<h3>Statut&nbsp;: en attente de paiement</h3>
		<p>{{:button shape="delete" name="cancel" label="Annuler" type="submit"}}</p>
		<p>{{:button shape="right" name="accept" label="Accepter et transformer en facture" type="submit"}}
	</div>
{{elseif $doc.status === 'ok' && $doc.type === 'quote'}}
	<div class="alert block">
		<h3>Statut&nbsp;: devis accepté</h3>
		<p>{{:button shape="delete" name="cancel" label="Annuler" type="submit"}}</p>
		<p>En cliquant sur ce bouton, le devis sera annulé.</p>
	</div>
{{elseif $doc.status === 'waiting' && $doc.type === 'invoice'}}
	<div class="alert block">
		<h3>Statut&nbsp;: en attente de paiement</h3>
		<p>{{:button shape="delete" name="cancel" label="Annuler" type="submit"}}</p>
		<p>En cliquant sur ce bouton, la facture sera annulée.<br />Son solde sera reporté en comptabilité comme abandon de créance.</p>
	</div>
{{/if}}

<dl class="describe">
	<dt>Numéro</dt>
	<dd>{{$doc.number|or:"En attente"}}</dd>
	<dt>Objet</dt>
	<dd>{{$doc.label}}</dd>
	<dt>Date</dt>
	<dd>{{$doc.date|date_short}}</dd>
	<dt>Date d'échéance</dt>
	<dd>{{$doc.date_expiry|date_short|or:'— Aucune —'}}</dd>
	<dt>Client</dt>
	<dd>
		<strong>{{$client.name}}</strong><br />
		{{$client.address|nl2br}}<br />
		{{:linkbutton shape="user" href="./client.html?key=%s"|args:$client.key label="Voir la fiche du client"}}
	</dd>
	<dt>Statut</dt>
	<dd>
		{{:call function="display_status_label" status=$doc.status type=$doc.type}}
	</dd>

	<dt>Montant total</dt>
	<dd><strong class="money">{{$total|money_currency:false:false}}</strong></dd>

	{{if $doc.id_transaction}}
	<dt>Écriture comptable</dt>
	<dd>{{:link class="num" href="!acc/transactions/details.php?id=%d"|args:$doc.id_transaction label="#%d"|args:$doc.id_transaction}}</dd>
	{{/if}}
</dl>

{{if $doc.status === 'draft'}}
	<p class="actions">
		{{:linkbutton shape="plus" label="Ajouter une ligne" href="./line.html?key=%s"|args:$doc.key target="_dialog"}}
	</p>
{{/if}}
{{#list
	select="$$.label AS 'Libellé'; $$.description AS 'Description'; $$.date AS 'Date'; $$.reference AS 'Réf. justificatif'; $$.category AS 'Catégorie'; $$.amount AS 'Montant'"
	order=1
	desc=false
	where="$$.type = 'line' AND $$.document = :document"
	:document=$doc.key
}}
	<tr>
		<th>{{$label}}</th>
		<td class="desc">{{$description|escape|nl2br}}</td>
		<td>{{$date|date_short}}</td>
		<td>{{$reference}}</td>
		<td>{{$category}}</td>
		<td class="money">{{$amount|money_currency}}</td>
		<td class="actions">
			{{if $doc.status === 'draft'}}
				{{:button name="delete_line" type="submit" value=$key label="Supprimer" shape="delete"}}
			{{/if}}
		</td>
	</tr>
{{else}}
	<p class="alert block">
		Aucune ligne dans ce document.<br />
	</p>
{{/list}}
</form>

{{if $doc.type === 'invoice' && $doc.status !== 'draft'}}
	<h2 class="ruler">Paiements (remboursements)</h2>

	{{if $doc.payments}}
		<table class="list">
			<thead>
				<tr>
					<td>Num.</td>
					<td>Date</td>
					<td>Libellé</td>
					<td class="money">Montant</td>
				</tr>
			</thead>
			<tbody>
			{{#transactions id=$doc.payments}}
				<tr>
					<td class="num">{{:link class="num" href="!acc/transactions/details.php?id=%d"|args:$id label="#%d"|args:$id}}</td>
					<td>{{$date|date_short}}</td>
					<td>{{$label}}</td>
					<td class="money">{{$credit|money_currency}}</td>
				</tr>
			{{/transactions}}
			</tbody>
		</table>
	{{/if}}

	{{if $doc.status === 'waiting'}}
		<p class="actions-center">
			{{:linkbutton shape="plus" label="Saisir un paiement" href="payment.html?doc=%s"|args:$doc.key target="_dialog"}}
		</p>
	{{/if}}
{{/if}}

{{:admin_footer}}
