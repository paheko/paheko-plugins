<nav class="tabs">
	<ul>
		<?php $type = isset($f) ? $f->type : ($_GET['type'] ?? null); ?>
		<li{if $current == 'home' && !$type} class="current"{/if}><a href="./">Campagnes</a></li>
		<li{if $current == 'home' && $type === 'Membership'} class="current"{/if}><a href="./?type=Membership">Adhésions</a></li>
		<li{if $current == 'home' && $type === 'Donation'} class="current"{/if}><a href="./?type=Donation">Dons</a></li>
		<li{if $current == 'sync'} class="current"{/if}><a href="sync.php">Synchronisation</a></li>
	{if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)}
		<li{if $current == 'config'} class="current"{/if}><a href="config.php">Configuration</a></li>
	{/if}
	</ul>

	{if $current === 'config'}
		<ul class="sub">
			<li{if $sub_current === 'users'} class="current"{/if}><a href="config.php">Membres</a></li>
			<li{if $sub_current === 'accounting'} class="current"{/if}><a href="config_accounting.php">Comptabilité</a></li>
			<li{if $sub_current === 'client'} class="current"{/if}><a href="config_client.php?{$dialog_qs}">Connexion à HelloAsso</a></li>
		</ul>
	{elseif isset($f->name)}
		{if !empty($show_export)}
		<aside>
			{exportmenu right=true}
		</aside>
		{/if}

		<ul class="sub">
			<li class="title">{$f.name}</li>
			<li{if $current_sub == 'orders'} class="current"{/if}>{link href="orders.php?id=%d"|args:$f.id label="Commandes"}</li>
			<li{if $current_sub == 'payments'} class="current"{/if}>{link href="payments.php?id=%d"|args:$f.id label="Paiements"}</li>
			<li{if $current_sub == 'items'} class="current"{/if}>{link href="items.php?id=%d"|args:$f.id label="Articles"}</li>
			{if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)}
				<li{if $current_sub === 'config'} class="current"{/if}>{link href="form.php?id=%d"|args:$f.id label="Configuration de la campagne"}</li>
			{/if}
		</ul>
	{/if}
</nav>
