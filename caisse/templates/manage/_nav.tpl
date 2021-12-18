<nav class="tabs">
	{if $current == 'products'}
	<aside>
		{linkbutton href="print.php" label="Fiche produits et tarifs" shape="print"}
		{linkbutton href="edit.php?new" label="Nouveau produit" shape="plus" target="_dialog"}
	</aside>
	{elseif $current == 'categories'}
	<aside>
		{linkbutton href="edit.php?new" label="Nouvelle catégorie" shape="plus" target="_dialog"}
	</aside>
	{elseif $current == 'stock'}
	<aside>
		{linkbutton href="edit.php?new" label="Nouvel événement" shape="plus" target="_dialog"}
	</aside>
	{/if}

	<ul>
		<li{if $current == ''} class="current"{/if}><a href="{$plugin_url}">Caisse</a></li>
		<li{if $current == 'stats'} class="current"{/if}><a href="{$plugin_url}manage/stats.php">Statistiques</a></li>
		<li{if $current == 'products'} class="current"{/if}><a href="{$plugin_url}manage/products/">Produits</a></li>
		<li{if $current == 'categories'} class="current"{/if}><a href="{$plugin_url}manage/categories/">Catégories</a></li>
		<li{if $current == 'stock'} class="current"{/if}><a href="{$plugin_url}manage/stock/">Stock</a></li>
		<li{if $current == 'export'} class="current"{/if}><a href="{$plugin_url}manage/export.php">Export compta CSV</a></li>
	</ul>
</nav>
