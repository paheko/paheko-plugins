<nav class="tabs">
	<aside>
		{{if $current === 'clients' && $client.key}}
			{{:linkbutton shape="edit" href="edit.html?key=%s"|args:$client.key label="Modifier"}}
			{{:linkbutton shape="delete" href="delete.html?key=%s"|args:$client.key label="Supprimer"}}
		{{elseif $current === 'clients'}}
			{{:linkbutton href="edit.html" label="Nouveau client" shape="plus"}}
		{{else}}
			{{#restrict section="config" level="admin"}}
				{{:linkbutton href="config.html" label="Configuration" shape="settings"}}
			{{/restrict}}
			{{:linkbutton href="edit.html?type=quote" label="Nouveau devis" shape="plus"}}
			{{:linkbutton href="edit.html?type=invoice" label="Nouvelle facture" shape="plus"}}
		{{/if}}
	</aside>

	<ul>
		<li{{if $current === 'index'}} class="current"{{/if}}><a href="{{$module.url}}">Tous les documents</a></li>
		<li{{if $current === 'drafts'}} class="current"{{/if}}><a href="{{$module.url}}?show=drafts">Brouillons</a></li>
		<li{{if $current === 'quotes'}} class="current"{{/if}}><a href="{{$module.url}}?show=quotes">Devis</a></li>
		<li{{if $current === 'payable'}} class="current"{{/if}}><a href="{{$module.url}}?show=payable">Factures en souffrance</a></li>
		<li{{if $current === 'paid'}} class="current"{{/if}}><a href="{{$module.url}}?show=paid">Factures réglées</a></li>
		<li{{if $current === 'clients'}} class="current"{{/if}}><a href="{{$module.url}}clients/">Clients</a></li>
	</ul>
</nav>