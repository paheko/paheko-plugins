{include file="_head.tpl" title="Configuration — %s"|args:$plugin.nom current="plugin_%s"|args:$plugin.id}

{form_errors}

<form method="post" action="{$self_url}">

    <fieldset>
        <legend>Configuration</legend>
        <dl>
            {input type="checkbox" name="display_hello" value="1" default=$plugin.config label="Afficher un message de coucou"}
        </dl>
    </fieldset>

    <p class="submit">
        {csrf_field key="config_plugin_%s"|args:$plugin.id}
        {button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
    </p>
</form>

{include file="_foot.tpl"}
