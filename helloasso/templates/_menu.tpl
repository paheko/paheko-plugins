<nav class="tabs">
	<ul>
		<li{if $current == 'home'} class="current"{/if}><a href="./">Formulaires</a></li>
		{*<li{if $current == 'targets'} class="current"{/if}><a href="targets.php">Synchronisation</a></li>*}
		{if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)}
			{*<li{if $current == 'config'} class="current"{/if}><a href="config.php">Configuration</a></li>*}
			<li{if $current == 'config_client'} class="current"{/if}><a href="config_client.php">Connexion à HelloAsso</a></li>
		{/if}
	</ul>

	{if !empty($form.name)}
		{if !empty($show_export)}
		<aside>
			{exportmenu right=true}
		</aside>
		{/if}

	<ul class="sub">
		<li class="title">{$form.name}</li>
		<li{if $current_sub == 'orders'} class="current"{/if}>{link href="orders.php?id=%d"|args:$form.id label="Commandes"}</li>
		<li{if $current_sub == 'payments'} class="current"{/if}>{link href="payments.php?id=%d"|args:$form.id label="Paiements"}</li>
		<li{if $current_sub == 'items'} class="current"{/if}>{link href="items.php?id=%d"|args:$form.id label="Articles"}</li>
	</ul>
	{/if}
</nav>
