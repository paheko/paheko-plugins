{if !$dialog}
{* On n'affiche la navigation que si on n'est pas dans une boîte de dialogue *}
<nav class="tabs">
	<ul>
		<li{if $current == 'index'} class="current"{/if}>{link href="./" label="Test"}</li>
		<li{if $current == 'config'} class="current"{/if}>{link href="config.php" label="Configuration"}</li>
	</ul>
</nav>
{/if}