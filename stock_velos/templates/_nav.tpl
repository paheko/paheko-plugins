<nav class="tabs">
	<aside>
		{if $current === 'index' || $current === 'historique'}
			{exportmenu}
		{/if}
		{if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)}
			{linkbutton shape="settings" href="config.php" label="Configuration"}
		{/if}
		{linkbutton shape="table" href="ajout_demontage.php" label="Saisie démontage"}
		{linkbutton shape="plus" href="ajout.php" label="Enregistrer un vélo"}
	</aside>
	<ul>
		<li class="{if $current == 'index'}current{/if}"><a href="./">Vélos en stock</a></li>
		<li class="{if $current == 'recherche'}current{/if}"><a href="recherche.php">Recherche</a></li>
		<li class="{if $current == 'stock'}current{/if}"><a href="stock.php">État du stock</a></li>
		<li class="{if $current == 'historique'}current{/if}"><a href="historique.php">Historique</a></li>
		<li class="{if $current == 'stats'}current{/if}"><a href="stats.php">Statistiques</a></li>
	</ul>
</nav>
