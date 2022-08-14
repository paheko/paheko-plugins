{include file="_head.tpl" title="État du stock de vélos" current="plugin_%s"|args:$plugin.id}

{include file="%s_nav.tpl"|args:$plugin_tpl current="stock"}

<section class="stock">
    <p><strong>{$total|escape}</strong> vélos en stock, dont :</p>
    <article class="etiquettes">
        <h3>{$a_demonter|count} à démonter</h3>
        <p>
            {foreach from=$a_demonter item="num"}
            <a href="{plugin_url file="fiche.php" query=1}etiquette={$num|escape}" class="a_demonter">{$num|escape}</a>
            {/foreach}
        </p>
        <h3>{$en_vente|count} en vente (valeur : {$valeur_vente|escape} €, prix moyen d'un vélo : {$prix_moyen|escape} €)</h3>
        <p>
            {foreach from=$en_vente key="num" item="prix"}
            <a href="{plugin_url file="fiche.php" query=1}etiquette={$num|escape}" class="en_vente">{$num|escape} <i>{$prix}&nbsp;€</i></a>
            {/foreach}
        </p>
        <h3>{$autres|count} divers en stock</h3>
        <p>
            {foreach from=$autres item="num"}
            <a href="{plugin_url file="fiche.php" query=1}etiquette={$num|escape}" class="en_stock">{$num|escape}</a>
            {/foreach}
        </p>
    </article>

    <article>
        <p>Bourse aux vélos :
            <a href="{plugin_url file="vente_tout.php"}">Imprimer des contrats de vente pour tous les vélos en vente</a>
        </p>
    </article>
</section>

{include file="_foot.tpl"}