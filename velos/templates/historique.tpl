{include file="admin/_head.tpl" title="`$total` vélos sortis du stock" current="plugin_`$plugin.id`"}

{include file="`$plugin_tpl`_nav.tpl" current="historique"}

<table class="list">
    <thead class="userOrder">
        <tr>
            <th class="{if $order == 'id'} cur {if $desc}desc{else}asc{/if}{/if}">Num. <a class="icn" href="{plugin_url query=1}&amp;o=id&amp;a">&uarr;</a><a class="icn" href="{plugin_url query=1}&amp;o=id&amp;d">&darr;</a></th>
            <td class="{if $order == 'type'}cur {if $desc}desc{else}asc{/if}{/if}">Type <a class="icn" href="{plugin_url query=1}&amp;o=type&amp;a">&uarr;</a><a class="icn" href="{plugin_url query=1}&amp;o=type&amp;d">&darr;</a></td>
            <td class="{if $order == 'roues'}cur {if $desc}desc{else}asc{/if}{/if}">Roues <a class="icn" href="{plugin_url query=1}&amp;o=roues&amp;a">&uarr;</a><a class="icn" href="{plugin_url query=1}&amp;o=roues&amp;d">&darr;</a></td>
            <td class="{if $order == 'genre'}cur {if $desc}desc{else}asc{/if}{/if}">Genre <a class="icn" href="{plugin_url query=1}&amp;o=genre&amp;a">&uarr;</a><a class="icn" href="{plugin_url query=1}&amp;o=genre&amp;d">&darr;</a></td>
            <td class="{if $order == 'modele'}cur {if $desc}desc{else}asc{/if}{/if}">Modèle <a class="icn" href="{plugin_url query=1}&amp;o=modele&amp;a">&uarr;</a><a class="icn" href="{plugin_url query=1}&amp;o=modele&amp;d">&darr;</a></td>
            <td class="{if $order == 'couleur'}cur {if $desc}desc{else}asc{/if}{/if}">Couleur <a class="icn" href="{plugin_url query=1}&amp;o=couleur&amp;a">&uarr;</a><a class="icn" href="{plugin_url query=1}&amp;o=couleur&amp;d">&darr;</a></td>
            <td class="{if $order == 'prix'}cur {if $desc}desc{else}asc{/if}{/if}">Prix <a class="icn" href="{plugin_url query=1}&amp;o=prix&amp;a">&uarr;</a><a class="icn" href="{plugin_url query=1}&amp;o=prix&amp;d">&darr;</a></td>
            <td class="{if $order == 'date_sortie'}cur {if $desc}desc{else}asc{/if}{/if}">Sortie <a class="icn" href="{plugin_url query=1}o=date_sortie&amp;a">&uarr;</a><a class="icn" href="{plugin_url query=1}o=date_sortie&amp;d">&darr;</a></td>
            <td class="{if $order == 'raison_sortie'}cur {if $desc}desc{else}asc{/if}{/if}">Raison <a class="icn" href="{plugin_url query=1}o=raison_sortie&amp;a">&uarr;</a><a class="icn" href="{plugin_url query=1}o=raison_sortie&amp;d">&darr;</a></td>
        </tr>
    </thead>
    <tbody>
    {foreach from=$liste item="velo"}
        <tr>
            <th class="num"><a href="{plugin_url query=1}id={$velo.id|escape}">{$velo.id|escape}</a></th>
            <td>{$velo.type|escape}</td>
            <td>{$velo.roues|escape}</td>
            <td>{$velo.genre|escape}</td>
            <td>{$velo.modele|escape}</td>
            <td>{$velo.couleur|escape}</td>
            <td>{if empty($velo.prix)}--{elseif $velo.prix < 0}à&nbsp;démonter{else}{$velo.prix|escape} €{/if}</td>
            <td>{$velo.date_sortie|format_sqlite_date_to_french}</td>
            <td>{$velo.raison_sortie|escape}</td>
        </tr>
    {/foreach}
    </tbody>
</table>

{include file="admin/_foot.tpl"}