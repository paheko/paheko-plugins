{include file="_head.tpl" title="Enregistrer un vélo" current="plugin_%s"|args:$plugin.id}

{include file="%s_nav.tpl"|args:$plugin_tpl current="ajout"}

<form method="post" action="{$self_url}">

{form_errors}

<fieldset>
    <legend>Général</legend>
    <dl>
        <dt>Numéro unique du vélo</dt>
        <dd>(Sera donné automatiquement à l'enregistrement du vélo.)</dd>
        <dt><label for="f_etiquette">Numéro étiquette</label> <b>(obligatoire)</b></dt>
        <dd>
            {input type="number" name="etiquette" required=true}
            <input type="button" onclick="document.getElementById('f_etiquette').value='{$libre|escape}';" value="Utiliser la première étiquette libre (n°{$libre|escape})" />
        </dd>
        {input type="text" name="bicycode" label="Bicycode"}
        <dt><label for="f_prix">Prix du vélo</label></dt>
        <dd>
            {input type="number" name="prix" min="-1" step="1"} €
            <input type="button" onclick="document.getElementById('f_prix').value='-1';" value="Vélo à démonter" />
            <input type="button" onclick="document.getElementById('f_prix').value='0';" value="Pas en vente" />
        </dd>
        {input type="textarea" name="notes" label="Notes"}
    </dl>
</fieldset>

<fieldset>
    <legend>Provenance</legend>
    <dl>
        {input type="select" name="source" label="D'où provient le vélo ?" required=true options=$sources default="Don"}
        {input type="text" name="source_details" label="Détails sur la provenance" help="pour le don : numéro d'adhérent ou nom du donneur" required=true}
    </dl>
</fieldset>

<fieldset>
    <legend>Description du vélo</legend>
    <dl>
        {input type="select" name="type" label="Type" required=true options=$types}
        {input type="select" name="roues" label="Taille des roues" required=true options=$roues}
        {input type="select" name="genre" label="Genre" required=true options=$genres}
        {input type="text" name="couleur" label="Couleur" required=true}
        {input type="text" name="modele" label="Marque et modèle" required=true}
    </dl>
</fieldset>

<fieldset>
    <legend>Entrée du vélo</legend>
    <dl>
        {input type="date" label="Date d'entrée dans le stock" name="date_entree" required=true default=$now}
        {input type="text" label="État à l'entrée dans le stock" name="etat_entree" required=true}
    </dl>
</fieldset>

<fieldset>
    <legend>Sortie du vélo</legend>
    <dl>
        {input type="date" label="Date de sortie" name="date_sortie"}
        {input type="select" label="Raison de sortie" name="raison_sortie" options=$raisons_sortie}
        {input type="text" label="Détails de sortie" name="details_sortie" help="Inscrire le numéro d'adhérent en cas de vente"}
    </dl>
</fieldset>

<p class="submit">
    {csrf_field key="ajout_velo"}
    <input type="submit" name="save" value="Enregistrer &rarr;" />
</p>
</form>

{include file="_foot.tpl"}