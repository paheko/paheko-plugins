<nav class="tabs">
	<ul>
		<li{if $current == 'home'} class="current"{/if}><a href="./">Formulaires</a></li>
		<li{if $current == 'sync'} class="current"{/if}><a href="sync.php">Synchronisation</a></li>
		{if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)}
			<li{if $current == 'config'} class="current"{/if}><a href="config.php">Configuration</a></li>
		{/if}
	</ul>

	{if $current === 'config'}
		<ul class="sub">
			<li{if $current == 'config'} class="current"{/if}><a href="config.php">Configuration</a></li>
			<li{if $current == 'config_client'} class="current"{/if}><a href="config_client.php">Connexion à HelloAsso</a></li>
		</ul>
	{elseif !empty($form.name)}
		<aside>
			{if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)}
				{linkbutton shape="settings" label="Configurer le formulaire" href="form.php?id=%d"|args:$form.id}
			{/if}
			{if !empty($show_export)}
				{exportmenu right=true}
			{/if}
		</aside>

		<ul class="sub">
			<li class="title">{$form.name}</li>
			<li{if $current_sub == 'orders'} class="current"{/if}>{link href="orders.php?id=%d"|args:$form.id label="Commandes"}</li>
			<li{if $current_sub == 'payments'} class="current"{/if}>{link href="payments.php?id=%d"|args:$form.id label="Paiements"}</li>
			<li{if $current_sub == 'items'} class="current"{/if}>{link href="items.php?id=%d"|args:$form.id label="Articles"}</li>
		</ul>
	{/if}
</nav>
