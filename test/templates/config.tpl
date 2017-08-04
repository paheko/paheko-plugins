{include file="admin/_head.tpl" title="Configuration — %s"|args:$plugin.nom current="plugin_%s"|args:$plugin.id}

{form_errors}

<form method="post" action="{$self_url}">

    <fieldset>
        <legend>Configuration</legend>
        <dl>
            <dt>
                <label>
                    <input type="checkbox" name="display_hello" value="1" {form_field name="display_hello" checked=1 data=$plugin.config} />
                    Afficher un message de coucou
                </label>
            </dt>
        </dl>
    </fieldset>

    <p class="submit">
        {csrf_field key="config_plugin_%s"|args:$plugin.id}
        <input type="submit" name="save" value="Enregistrer &rarr;" />
    </p>
</form>

{include file="admin/_foot.tpl"}
