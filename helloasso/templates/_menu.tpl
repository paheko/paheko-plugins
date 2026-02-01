<nav class="tabs">
	{if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)}
		<aside>
			{linkbutton href="config.php" label="Configuration" shape="settings" target="_dialog"}
		</aside>
	{/if}

	<ul>
		<?php $type ??= null; ?>
		<li{if $current == 'home' && !$type} class="current"{/if}><a href="./">Campagnes</a></li>
		<li{if $current == 'home' && $type === 'Membership'} class="current"{/if}><a href="./?type=Membership">Adhésions</a></li>
		<li{if $current == 'home' && $type === 'Donation'} class="current"{/if}><a href="./?type=Donation">Dons</a></li>
		<li{if $current == 'sync'} class="current"{/if}><a href="sync.php">Synchronisation</a></li>
	</ul>

	{if isset($f->name)}
		<aside>
			{if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)}
				{linkbutton href="tiers.php?id=%d"|args:$f.id label="Configurer les tarifs" shape="menu" target="_dialog"}
				{linkbutton href="form.php?id=%d"|args:$f.id label="Configurer la campagne" shape="edit" target="_dialog"}
			{/if}
		{if !empty($show_export)}
			{exportmenu right=true}
		{/if}
		</aside>

		<ul class="sub">
			<li class="title">{$f.name}</li>
			<li{if $current_sub == 'orders'} class="current"{/if}>{link href="orders.php?id=%d"|args:$f.id label="Commandes"}</li>
			<li{if $current_sub == 'payments'} class="current"{/if}>{link href="payments.php?id=%d"|args:$f.id label="Paiements"}</li>
			<li{if $current_sub == 'items'} class="current"{/if}>{link href="items.php?id=%d"|args:$f.id label="Articles"}</li>
		</ul>
	{/if}
</nav>
