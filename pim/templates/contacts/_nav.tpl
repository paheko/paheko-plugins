<nav class="tabs">
	<aside>
		{linkbutton shape="print" label="Imprimer" href="print.php"}
		{linkbutton shape="import" label="Import" href="upload.php"}
		{linkbutton shape="export" label="Export" href="export.php"}
		{linkbutton shape="plus" label="Nouveau contact" href="edit.php"}
	</aside>
	<ul>
		<li><a href="../">Agenda</a></li>
		<li class="current"><a href="./">Contacts</a></li>
		<li><a href="../config/categories/">Configuration</a></li>
	</ul>
	<ul class="sub">
		<li {if $archived === false} class="current"{/if}><a href="./">Contacts actuels</a></li>
		<li {if $archived === true} class="current"{/if}><a href="./?archived">Contacts archivés</a></li>
	</ul>
</nav>
