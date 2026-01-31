{{:include file="./_defines.tpl"}}

{{if $_GET.show === 'payable'}}
	{{:assign title="Factures en souffrance"}}
{{elseif $_GET.show === 'paid'}}
	{{:assign title="Factures réglées"}}
{{elseif $_GET.show === 'drafts'}}
	{{:assign title="Brouillons"}}
{{elseif $_GET.show === 'quotes'}}
	{{:assign title="Devis"}}
{{else}}
	{{:assign title="Devis et factures"}}
{{/if}}

{{:admin_header title=$title current="acc"}}

{{:include file="./_nav.html" current=$_GET.show|or:"index"}}

{{if $_GET.show == 'quotes'}}
	{{:assign filter="$$.type = 'quote'"}}
{{elseif $_GET.show == 'paid'}}
	{{:assign filter="$$.type = 'invoice' AND $$.status = 'paid'"}}
{{elseif $_GET.show == 'payable'}}
	{{:assign filter="$$.type = 'invoice' AND $$.status = 'payable'"}}
{{elseif $_GET.show == 'drafts'}}
	{{:assign filter="$$.status = 'draft'"}}
{{else}}
	{{:assign filter="1"}}
{{/if}}

{{if $_GET.show === 'drafts' || !$_GET.show}}
	{{:assign first_column="CASE $$.type WHEN 'invoice' THEN 'Facture' ELSE 'Devis' END AS 'Type'; "}}
{{/if}}

{{#list select="%s $$.ref AS 'Numéro'; $$.date AS 'Date'; $$.date_expiry AS 'Échéance'; (SELECT c.$$.name FROM @TABLE AS c WHERE c.$$.type = 'client' AND key = @TABLE.$$.client) AS 'Client'; $$.total AS 'Montant'; NULL AS 'Statut'"|args:$first_column where="($$.type = 'invoice' OR $$.type = 'quote') AND "|cat:$filter}}
		<tr>
			{{if $first_column}}
			<td>{{$col1}}</td>
			{{/if}}
			<th>{{if !$number}}<em>(Brouillon)</em>{{else}}{{$number}}{{/if}}</th>
			<td>{{$date|date_short}}</td>
			<td>{{$date_expiry|date_short}}</td>
			<td>{{if $first_column}}{{$col5}}{{else}}{{$col4}}{{/if}}</td>
			<td class="money">{{$total|raw|money_currency}}</td>
			<td>
				{{:call function="display_status_label"}}
			</td>
			<td class="actions">
				{{:linkbutton shape="menu" label="Détails" href="details.html?doc=%s"|args:$key}}
			</td>
		</tr>
{{else}}
	<p class="alert block">Aucun document ici.</p>
{{/list}}

{{:admin_footer}}
