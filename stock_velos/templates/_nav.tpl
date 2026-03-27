<nav class="tabs">
	<aside>
		{if $current === 'index' || $current === 'historique'}
			{exportmenu}
		{/if}
		{linkbutton shape="table" href="ajout_demontage.php" label="Démontage"}
		{linkbutton shape="plus" href="ajout.php" label="Enregistrer vélo"}
	</aside>
	<ul>
		<li class="{if $current == 'index'}current{/if}"><a href="./">Vélos en stock</a></li>
		<li class="{if $current == 'stock'}current{/if}"><a href="stock.php">État du stock</a></li>
		<li class="{if $current == 'historique'}current{/if}"><a href="historique.php">Historique</a></li>
		<li class="{if $current == 'stats'}current{/if}"><a href="stats.php">Statistiques</a></li>
		<li class="{if $current == 'recherche'}current{/if}"><a href="recherche.php">Recherche</a></li>
		{if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)}
			<li class="{if $current == 'config'}current{/if}"><a href="config.php">Configuration</a></li>
		{/if}
	</ul>
</nav>
