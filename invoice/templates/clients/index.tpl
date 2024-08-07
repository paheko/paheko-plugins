{{:admin_header title="Clients" current="acc"}}

{{:include file="../_nav.html" current="clients"}}

<nav class="tabs">
	<ul class="sub">
		<li {{if !$_GET.archived}}class="current"{{/if}}>{{:link href="./" label="Actuels"}}</li>
		<li {{if $_GET.archived}}class="current"{{/if}}>{{:link href="./?archived=1" label="Archivés"}}</li>
	</ul>
</nav>

{{if $_GET.archived}}
	{{:assign filter="$$.archived = 1"}}
{{else}}
	{{:assign filter="$$.archived = 0"}}
{{/if}}

{{#list select="$$.name AS 'Nom'" where="$$.type = 'client' AND "|cat:$filter}}
		<tr>
			<th>{{:link href="details.html?key=%s"|args:$key label=$name}}</th>
			<td class="actions">
				{{:linkbutton shape="user" label="Détails" href="details.html?key=%s"|args:$key}}
				{{:linkbutton shape="edit" label="Modifier" href="edit.html?key=%s"|args:$key}}
			</td>
		</tr>
{{else}}
	<p class="alert block">Aucun client n'existe dans cette liste.</p>
{{/list}}

{{:admin_footer}}
