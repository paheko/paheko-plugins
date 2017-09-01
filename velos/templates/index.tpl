{include file="admin/_head.tpl" title="`$total` vélos en stock" current="plugin_`$plugin.id`"}

{include file="`$plugin_tpl`_nav.tpl" current="index"}

<form method="get" action="{plugin_url file="fiche.php"}" class="fastFind">
    <fieldset>
        <legend>Trouver un vélo par numéro d'étiquette</legend>
        <p>
            <input type="number" size="5" name="etiquette" />
            <input type="submit" value="Trouver" />
        </p>
    </fieldset>
    <fieldset>
        <legend>Trouver un vélo par numéro unique</legend>
        <p>
            <input type="number" size="5" name="id" />
            <input type="submit" value="Trouver" />
        </p>
    </fieldset>
</form>

<table class="list">
    <thead class="userOrder">
        <tr>
            <th class="{if $order == 'id'} cur {if $desc}desc{else}asc{/if}{/if}">Num. <a class="icn up" href="{plugin_url query=1}&amp;o=id&amp;a">&uarr;</a><a class="icn dn" href="{plugin_url query=1}&amp;o=id&amp;d">&darr;</a></th>
            <td class="{if $order == 'etiquette'} cur {if $desc}desc{else}asc{/if}{/if}">Étiq. <a class="icn up" href="{plugin_url query=1}&amp;o=etiquette&amp;a">&uarr;</a><a class="icn dn" href="{plugin_url query=1}&amp;o=etiquette&amp;d">&darr;</a></td>
            <td class="{if $order == 'type'}cur {if $desc}desc{else}asc{/if}{/if}">Type <a class="icn up" href="{plugin_url query=1}&amp;o=type&amp;a">&uarr;</a><a class="icn dn" href="{plugin_url query=1}&amp;o=type&amp;d">&darr;</a></td>
            <td class="{if $order == 'roues'}cur {if $desc}desc{else}asc{/if}{/if}">Roues <a class="icn up" href="{plugin_url query=1}&amp;o=roues&amp;a">&uarr;</a><a class="icn dn" href="{plugin_url query=1}&amp;o=roues&amp;d">&darr;</a></td>
            <td class="{if $order == 'genre'}cur {if $desc}desc{else}asc{/if}{/if}">Genre <a class="icn up" href="{plugin_url query=1}&amp;o=genre&amp;a">&uarr;</a><a class="icn dn" href="{plugin_url query=1}&amp;o=genre&amp;d">&darr;</a></td>
            <td class="{if $order == 'modele'}cur {if $desc}desc{else}asc{/if}{/if}">Modèle <a class="icn up" href="{plugin_url query=1}&amp;o=modele&amp;a">&uarr;</a><a class="icn dn" href="{plugin_url query=1}&amp;o=modele&amp;d">&darr;</a></td>
            <td class="{if $order == 'couleur'}cur {if $desc}desc{else}asc{/if}{/if}">Couleur <a class="icn up" href="{plugin_url query=1}&amp;o=couleur&amp;a">&uarr;</a><a class="icn dn" href="{plugin_url query=1}&amp;o=couleur&amp;d">&darr;</a></td>
            <td class="{if $order == 'prix'}cur {if $desc}desc{else}asc{/if}{/if}">Prix <a class="icn up" href="{plugin_url query=1}&amp;o=prix&amp;a">&uarr;</a><a class="icn dn" href="{plugin_url query=1}&amp;o=prix&amp;d">&darr;</a></td>
            <td class="{if $order == 'date_entree'}cur {if $desc}desc{else}asc{/if}{/if}">Entrée <a class="icn up" href="{plugin_url query=1}&amp;o=date_entree&amp;a">&uarr;</a><a class="icn dn" href="{plugin_url query=1}&amp;o=date_entree&amp;d">&darr;</a></td>
        </tr>
    </thead>
    <tbody>
    {foreach from=$liste item="velo"}
        <tr>
            <th class="num"><a href="{plugin_url query=1}id={$velo.id|escape}">{$velo.id|escape}</a></th>
            <td class="num"><a href="{plugin_url query=1}id={$velo.id|escape}">{$velo.etiquette|escape}</a></td>
            <td>{$velo.type|escape}</td>
            <td>{$velo.roues|escape}</td>
            <td>{$velo.genre|escape}</td>
            <td>{$velo.modele|escape}</td>
            <td>{$velo.couleur|escape}</td>
            <td>{if empty($velo.prix)}--{elseif $velo.prix < 0}à&nbsp;démonter{else}{$velo.prix|escape} €{/if}</td>
            <td>{$velo.date_entree|format_sqlite_date_to_french}</td>
        </tr>
    {/foreach}
    </tbody>
</table>

{include file="admin/_foot.tpl"}