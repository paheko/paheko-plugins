{include file="_head.tpl" title="Configuration — %s"|args:$plugin.nom current="plugin_%s"|args:$plugin.id}

{form_errors}

<form method="post" action="{$self_url|escape}">

    <fieldset>
        <legend>Heures d'ouvertures</legend>
        <table class="list">
            <thead>
                <tr>
                    <th>Jour</th>
                    <td>De</td>
                    <td>À</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                {foreach from=$plugin_config->open key="day" item="hours"}
                <tr>
                    <th>{html_opening_day_select value=$day}</th>
                    <td>{html_opening_hour_select value=$hours[0] start_end="start"}</td>
                    <td>{html_opening_hour_select value=$hours[1] start_end="end"}</td>
                    <td class="actions"><a href="#unsupported" onclick="return removeRow(this);" class="icn" title="Supprimer cette ligne">➖</a></td>
                </tr>
                {/foreach}
            </tbody>
        </table>
        <p class="actions"><a href="#unsupported" onclick="return addRow(this);" class="icn" title="Ajouter une ligne">➕</a></p>
    </fieldset>

    <fieldset>
        <legend>Jours de fermeture</legend>
        <table class="list">
            <thead>
                <tr>
                    <td>Du</td>
                    <td>Au</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                {foreach from=$plugin_config->closed item="days"}
                <tr>
                    <td>{html_closing_day_select value=$days[0] start_end="start"}</td>
                    <td>{html_closing_day_select value=$days[1] start_end="end"} inclus</td>
                    <td class="actions"><a href="#unsupported" onclick="return removeRow(this);" class="icn" title="Supprimer cette ligne">➖</a></td>
                </tr>
                {/foreach}
            </tbody>
        </table>
        <p class="actions"><a href="#unsupported" onclick="return addRow(this);" class="icn" title="Ajouter une ligne">➕</a></p>
    </fieldset>

    <p class="submit">
        {csrf_field key="config_plugin_%s"|args:$plugin.id}
        <input type="submit" name="save" value="Enregistrer &rarr;" />
    </p>

    <script type="text/javascript">
    {literal}
    function removeRow(e) {
        var row = e.parentNode.parentNode;
        var table = row.parentNode.parentNode;

        if (table.rows.length == 1)
        {
            return false;
        }

        row.parentNode.removeChild(row);
        return false;
    }
    function addRow(e) {
        var table = e.parentNode.parentNode.querySelector('table');
        var row = table.rows[table.rows.length-1];
        row.parentNode.appendChild(row.cloneNode(true));
        return false;
    }
    {/literal}
    </script>
</form>

<div class="help">
    <h3>Exemple d'utilisation des boucles d'affichage des ouvertures</h3>
    <pre>{$example}</pre>
</div>

{include file="_foot.tpl"}
