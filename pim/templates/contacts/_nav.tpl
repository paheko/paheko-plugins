<nav class="tabs">
	<aside>
		{linkbutton shape="print" label="Imprimer" href="print.php"}
		{linkbutton shape="export" label="Export" href="export.php"}
		{linkbutton shape="plus" label="Nouveau contact" href="edit.php"}
	</aside>
	<ul>
		<li {if $archived === false} class="current"{/if}><a href="./">Contacts</a></li>
		<li {if $archived === true} class="current"{/if}><a href="./?archived">Contacts archivés</a></li>
	</ul>
</nav>
