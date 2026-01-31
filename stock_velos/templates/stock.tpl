{include file="_head.tpl" title="État du stock de vélos"}

{include file="./_nav.tpl" current="stock"}

<section class="stock">
	<p><strong>{$total|escape}</strong> vélos en stock, dont :</p>
	<article class="etiquettes">
		<h2 class="ruler">{$a_demonter|count} à démonter</h2>
		<p>
			{foreach from=$a_demonter item="r"}
			<a href="fiche.php?id={$r.id}" class="a_demonter">{if $r.etiquette}{$r.etiquette}{else}&#x1F6B2;{/if}</a>
			{/foreach}
		</p>
	</article>
	<article class="etiquettes">
		<h2 class="ruler">{$en_vente|count} en vente</h2>
		<h3>(valeur : {$valeur_vente|escape} €, prix moyen d'un vélo : {$prix_moyen|escape} €)</h3>
		<p>
			{foreach from=$en_vente item="r"}
			<a href="fiche.php?id={$r.id}" class="en_vente">{if $r.etiquette}{$r.etiquette}{else}&#x1F6B2;{/if} <i>{$r.prix}&nbsp;€</i></a>
			{/foreach}
		</p>
	</article>
	<article class="etiquettes">
		<h2 class="ruler">{$autres|count} divers en stock</h2>
		<p>
			{foreach from=$autres item="r"}
			<a href="fiche.php?id={$r.id}" class="en_stock">{if $r.etiquette}{$r.etiquette}{else}&#x1F6B2;{/if}</a>
			{/foreach}
		</p>
	</article>

	<article>
		<p>Bourse aux vélos :
			<a href="vente_tout.php">Imprimer des contrats de vente pour tous les vélos en vente</a>
		</p>
	</article>
</section>

{include file="_foot.tpl"}