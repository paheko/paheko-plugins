<nav class="tabs">
	<aside>
	{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)}
		{if $current === 'clients' && isset($client->key)}
			{linkbutton shape="edit" href="edit.php?id=%s"|args:$client.id label="Modifier"}
			{linkbutton shape="delete" href="delete.php?key=%d"|args:$client.id label="Supprimer"}
		{elseif $current === 'clients'}
			{linkbutton href="edit.php" label="Ajouter un client" shape="plus"}
		{else}
			{if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)}
				{linkbutton href="config.php" label="Configuration" shape="settings"}
			{/if}
			{if $current !== 'invoices'}
				{linkbutton href="edit.php?type=231" label="Créer un devis" shape="plus"}
			{/if}
			{if $current !== 'quotes'}
				{linkbutton href="edit.php?type=380" label="Créer une facture" shape="plus"}
			{/if}
		{/if}
	{/if}
	</aside>

	<ul>
		{tabitem selected=$current name="all" href="!p/invoice/" label="Tous les documents"}
		{tabitem selected=$current name="invoices" href="!p/invoice/?type=380" label="Factures"}
		{tabitem selected=$current name="quotes" href="!p/invoice/?type=231" label="Devis"}
		{tabitem selected=$current name="credits" href="!p/invoice/?type=381" label="Avoirs"}
		{tabitem selected=$current name="clients" href="!p/invoice/clients/" label="Clients"}
	</ul>
</nav>