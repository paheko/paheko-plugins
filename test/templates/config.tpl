{include file="_head.tpl" title="Configuration — %s"|args:$plugin.label}

{include file="./_nav.tpl" current="config"}

{form_errors}

<form method="post" action="{$self_url}">

    <fieldset>
        <legend>Configuration</legend>
        <dl>
            {input type="checkbox" name="display_button" value="1" source=$plugin.config label="Afficher le bouton sur la page d'accueil"}
        </dl>
    </fieldset>

    <p class="submit">
        {csrf_field key=$csrf_key}
        {button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
    </p>
</form>

{include file="_foot.tpl"}
