{include file="admin/_head.tpl" title="Chercher un vélo" current="plugin_`$plugin.id`"}

{include file="`$plugin_tpl`_nav.tpl" current="recherche"}

<ul class="sub_actions">
    <li><a href="{plugin_url file="sql.php"}">Recherche SQL</a></li>
</ul>

<form method="get" action="{$self_url}">
    <fieldset>
        <legend>Rechercher un vélo</legend>
        <dl>
            <dt><label for="f_field">Dont le champ...</label></dt>
            <dd>
                <select name="f" id="f_field">
                {foreach from=$fields key="field" value="name"}
                    <option value="{$field|escape}"{if $field == $current_field} selected="selected"{/if}>{$name|escape}</option>
                {/foreach}
                </select>
            </dd>
            <dt><label for="f_query">Contient ou correspond à...</label></dt>
            <dd>
                <input type="text" name="q" value="{$query|escape}" id="f_query" />
            </dd>
        </dl>
        <p class="submit">
            <input type="submit" value="Chercher" />
        </p>
    </fieldset>
</form>

{if empty($liste)}
    <p class="alert">Aucun vélo trouvé.</p>
{else}
    <h2>{$liste|@count} vélos trouvés</h2>
    <table class="list">
        <thead>
            <tr>
                <th class="num">Num.</th>
                <td class="num">Stock</td>
                <td class="cur">{$fields[$current_field]}</td>
                <td>Type</td>
                <td>Roues</td>
                <td>Genre</td>
                <td>Prix</td>
                <td>Entrée</td>
                <td>Sortie</td>
            </tr>
        </thead>
        <tbody>
        {foreach from=$liste item="velo"}
            <tr>
                <th class="num"><a href="{plugin_url query=1}id={$velo.id|escape}">{$velo.id|escape}</a></th>
                <td class="num">{if is_null($velo.date_sortie)}<a href="{plugin_url query=1}id={$velo.id|escape}">{$velo.etiquette|escape}</a>{else}[{$velo.raison_sortie|escape}]{/if}</td>
                <td>{$velo[$current_field]|escape}</td>
                <td>{$velo.type|escape}</td>
                <td>{$velo.roues|escape}</td>
                <td>{$velo.genre|escape}</td>
                <td>{if empty($velo.prix)}--{elseif $velo.prix < 0}à&nbsp;démonter{else}{$velo.prix|escape} €{/if}</td>
                <td>{$velo.date_entree|format_sqlite_date_to_french}</td>
                <td>{if !is_null($velo.date_sortie)}{$velo.date_sortie|format_sqlite_date_to_french}{/if}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
{/if}

{include file="admin/_foot.tpl"}