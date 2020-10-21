<nav class="tabs">
	<ul>
		<li class="{if $current == 'index'}current{/if}"><a href="{plugin_url}">Vélos en stock</a></li>
		<li class="{if $current == 'ajout'}current{/if}"><a href="{plugin_url file="ajout.php"}"><b>Enregistrer un vélo</b></a></li>
		<li class="{if $current == 'recherche'}current{/if}"><a href="{plugin_url file="recherche.php"}">Chercher</a></li>
		<li class="{if $current == 'stock'}current{/if}"><a href="{plugin_url file="stock.php"}">État du stock</a></li>
		<li class="{if $current == 'historique'}current{/if}"><a href="{plugin_url file="historique.php"}">Historique</a></li>
	</ul>
</nav>
