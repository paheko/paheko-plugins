<nav class="tabs">
	{if $current == 'products'}
	<aside>
		{linkbutton href="print.php" label="Fiche produits et tarifs" shape="print"}
		{linkbutton href="edit.php?new" label="Nouveau produit" shape="plus" target="_dialog"}
	</aside>
	{elseif $current == 'history'}
	<aside>
		{if $events_only}
			{linkbutton href="?id=%d"|args:$product.id label="Afficher tous les événements" shape="eye"}
		{else}
			{linkbutton href="?id=%d&events_only"|args:$product.id label="Cacher les ventes" shape="eye-off"}
		{/if}
			{exportmenu right=true}
	</aside>
	{elseif $current === 'sync'}
	<aside>
		{linkbutton shape="export" href="export.php" label="Export" target="_dialog"}
	</aside>
	{elseif $current == 'categories'}
	<aside>
		{linkbutton href="edit.php?new" label="Nouvelle catégorie" shape="plus"}
	</aside>
	{elseif $current == 'methods'}
	<aside>
		{linkbutton href="edit.php?new" label="Nouveau moyen de paiement" shape="plus"}
	</aside>
	{elseif $current == 'stock' && $subcurrent === 'history'}
	<aside>
		{exportmenu right=true}
	</aside>
	{elseif $current == 'stock'}
	<aside>
		{linkbutton href="edit.php?new" label="Nouvel événement" shape="plus" target="_dialog"}
	</aside>
	{elseif $current === 'config' && $subcurrent === 'locations'}
		<aside>
			{linkbutton href="edit.php?new" label="Ajouter un lieu" shape="plus"}
		</aside>
	{/if}

	<ul>
		<li{if $current == ''} class="current"{/if}><a href="{$plugin_admin_url}">{icon style="display: inline-block" shape="left"} Caisse</a></li>
		<li{if $current == 'stats'} class="current"{/if}><a href="{$plugin_admin_url}manage/stats.php">Statistiques</a></li>
		<li{if $current == 'products'} class="current"{/if}><a href="{$plugin_admin_url}manage/products/">Produits</a></li>
		<li{if $current == 'categories'} class="current"{/if}><a href="{$plugin_admin_url}manage/categories/">Catégories</a></li>
		<li{if $current == 'methods'} class="current"{/if}><a href="{$plugin_admin_url}manage/methods/">Moyens de paiement</a></li>
		<li{if $current == 'stock' || $current === 'history'} class="current"{/if}><a href="{$plugin_admin_url}manage/stock/">Stock</a></li>
		<li{if $current == 'sync'} class="current"{/if}><a href="{$plugin_admin_url}manage/sync.php">Comptabilité</a></li>
		{if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)}
		<li{if $current == 'config'} class="current"{/if}><a href="{$plugin_admin_url}config.php">Configuration</a></li>
		{/if}
	</ul>

	{if $current === 'stock'}
	<ul class="sub">
		<li {if $subcurrent === 'products'}class="current"{/if}><a href="./">Stock des produits</a></li>
		<li {if $subcurrent === 'events'}class="current"{/if}><a href="events.php">Événéments de stock</a></li>
		<li {if $subcurrent === 'history'}class="current"{/if}><a href="history.php">Historique complet</a></li>
	</ul>
	{elseif $current === 'config'}
		<ul class="sub">
			<li {if $subcurrent === 'locations'}class="current"{/if}><a href="{$plugin_admin_url}manage/locations/">Lieux de vente</a></li>
		</ul>
	{/if}
</nav>
