{include file="admin/_head.tpl" title="Enregistrer vélos démontés" current="plugin_%s"|args:$plugin.id}

{include file="%s_nav.tpl"|args:$plugin_tpl current="ajout_demontage"}

<form method="post" action="{$self_url}">

{form_errors}

<fieldset>
    <legend>Général</legend>
    <dl>
        <dd class="help">Ce formulaire permet d'enregistrer plusieurs vélos qui seront créés et directement marqués comme démontés.</dd>
        {input type="number" step="1" name="nb" label="Nombre de vélos à enregistrer" required=1}
        {input type="select" name="source" label="D'où provient le vélo ?" required=1 default="Partenariat" options=$sources}
        {input type="text" name="source_details" label="Détails sur la provenance"}
    </dl>
</fieldset>

<p class="submit">
    {csrf_field key="ajout_velos"}
    <input type="submit" name="save" value="Enregistrer &rarr;" />
</p>
</form>

{include file="admin/_foot.tpl"}