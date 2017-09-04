{include file="admin/_head.tpl" title="Recherche SQL" current="plugin_%s"|args:$plugin.id}

{include file="%s_nav.tpl"|args:$plugin_tpl current="recherche"}

<form method="get" action="" class="sql">
    <fieldset>
        <legend>Recherche SQL</legend>
        <pre class="help">{$schema|escape}</pre>
        <dl>
            <dt>SELECT</dt>
            <dd><textarea name="f" cols="50" rows="3">{$fields|escape}</textarea></dd>
            <dt>FROM velos</dt>
            <dd><textarea name="q" cols="50" rows="5">{$query|escape}</textarea></dd>
        </dl>
        <p class="submit">
            <input type="submit" value="Requête" />
        </p>
    </fieldset>
</form>

{form_errors}

{if empty($result)}
    <p class="alert">Aucun résultat.</p>
{else}
    <h2>{$result|@count} résultats trouvés</h2>
    <table class="list">
        <thead>
            <tr>
            {foreach from=$result[0] item="value" key="name"}
                {if $name == 'id'}
                    <th class="num">{$name|escape}</th>
                {else}
                    <td>{$name|escape}</td>
                {/if}
            {/foreach}
            </tr>
        </thead>
        <tbody>
        {foreach from=$result item="row"}
            <tr>
            {foreach from=$row item="value" key="name"}
                {if $name == 'id'}
                    <th class="num"><a href="{plugin_url query=1}id={$value|escape}">{$value|escape}</a></th>
                {elseif is_null($value)}
                    <td>NULL</td>
                {elseif substr($name, 0, 4) == 'date'}
                    <td>{$value|format_sqlite_date_to_french}</td>
                {else}
                    <td>{$value|escape}</td>
                {/if}
            {/foreach}
            </tr>
        {/foreach}
        </tbody>
    </table>
{/if}

{include file="admin/_foot.tpl"}