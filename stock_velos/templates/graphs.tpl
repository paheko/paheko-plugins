{include file="_head.tpl" title="Statistiques"}

{include file="./_nav.tpl" current="stats"}

<nav class="tabs">
	<ul class="sub">
		<li>{link href="stats.php" label="Tableaux"}</li>
		<li class="current">{link href="graphs.php" label="Graphiques"}</li>
	</ul>
</nav>

<figure class="graph">
	<figcaption><h2 class="ruler">Entrées et sorties</h2></figcaption>
	<img src="?graph=years" alt="" />
</figure>

<figure class="graph">
	<figcaption><h2 class="ruler">Sorties, par motif</h2></figcaption>
	<img src="?graph=exit" alt="" />
</figure>

<figure class="graph">
	<figcaption><h2 class="ruler">Entrées, par provenance</h2></figcaption>
	<img src="?graph=entry" alt="" />
</figure>

{include file="_foot.tpl"}