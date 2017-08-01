{include file="admin/_head.tpl" title="Configuration — %s"|args:$plugin.nom current="plugin_%s"|args:$plugin.id}

{form_errors}

<form method="post" action="{$self_url|escape}">

    <fieldset>
        <legend>Heures d'ouvertures</legend>
        <table>
            <thead>
                <tr>
                    <th>Jour</th>
                    <td>De</td>
                    <td>À</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                {foreach from=$openings key="day" value="hours"}
                <tr>
                    <th>{html_opening_day_select value=$day}</th>
                    <td>{html_opening_hour_select value=$hours[0]}</td>
                    <td>{html_opening_hour_select value=$hours[1]}</td>
                    <td class="actions"><a href="#unsupported" onclick="return removeRow(this);" class="icn" title="Supprimer cette ligne">➖</a></td>
                </tr>
                {/foreach}
            </tbody>
        </table>
        <p class="actions"><a href="#unsupported" onclick="return addRow(this);" class="icn" title="Ajouter une ligne">➕</a></p>
    </fieldset>

    <fieldset>
        <legend>Jours de fermeture</legend>
        <table>
            <thead>
                <tr>
                    <td>Du</td>
                    <td>Au</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                {foreach from=$closings value="days"}
                <tr>
                    <td>{html_closing_day_select value=$day[0] start_end="start"}</td>
                    <td>{html_closing_day_select value=$day[1] start_end="end"} inclus</td>
                    <td class="actions"><a href="#unsupported" onclick="return removeRow(this);" class="icn" title="Supprimer cette ligne">➖</a></td>
                </tr>
                {/foreach}
            </tbody>
        </table>
        <p class="actions"><a href="#unsupported" onclick="return addRow(this);" class="icn" title="Ajouter une ligne">➕</a></p>
    </fieldset>


    <fieldset>
        <legend><label for="f_tz">Fuseau horaire</label></legend>
        <dl>
            <dd>{html_timezone_select value=$tz}</dd>
        </dl>
    </fieldset>

    <p class="submit">
        {csrf_field key="config_plugin_%s"|args:$plugin.id}
        <input type="submit" name="save" value="Enregistrer &rarr;" />
    </p>
</form>

<p class="help">
    <pre>{$example}</pre>
</p>

{include file="admin/_foot.tpl"}
