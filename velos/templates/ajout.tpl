{include file="admin/_head.tpl" title="Enregistrer un vélo" current="plugin_`$plugin.id`" js=1}

{include file="`$plugin_tpl`_nav.tpl" current="ajout"}

<form method="post" action="{$self_url}">
{if !empty($error)}
    <p class="error">{$error|escape}</p>
{/if}

<fieldset>
    <legend>Général</legend>
    <dl>
        <dt>Numéro unique du vélo</dt>
        <dd>(Sera donné automatiquement à l'enregistrement du vélo.)</dd>
        <dt><label for="f_etiquette">Numéro étiquette</label> <b>(obligatoire)</b></dt>
        <dd>
            <input type="number" name="etiquette" id="f_etiquette" value="{form_field name=etiquette}" required="required" />
            <input type="button" onclick="document.getElementById('f_etiquette').value='{$libre|escape}';" value="Utiliser la première étiquette libre (n°{$libre|escape})" />
        </dd>
        <dt><label for="f_bicycode">Bicycode</label></dt>
        <dd><input type="number" name="bicycode" id="f_bicycode" value="{form_field name=bicycode}" /></dd>
        <dt><label for="f_prix">Prix du vélo</label></dt>
        <dd>
            <input type="number" name="prix" id="f_prix" min="-1" value="{form_field name=prix}" /> €
            <input type="button" onclick="document.getElementById('f_prix').value='-1';" value="Vélo à démonter" />
            <input type="button" onclick="document.getElementById('f_prix').value='0';" value="Pas en vente" />
        </dd>
        <dt><label for="f_notes">Notes</label></dt>
        <dd><textarea name="notes" id="f_notes" cols="70" rows="10">{form_field name=notes}</textarea></dd>
    </dl>
</fieldset>

<fieldset>
    <legend>Provenance</legend>
    <dl>
        <dt><label for="f_source">D'où provient le vélo ?</label> <b>(obligatoire)</b></dt>
        <dd>{form_select name=source values=$sources}</dd>
        <dt><label for="f_source_details">Détails sur la provenance</label> (pour le don : numéro d'adhérent ou nom du donneur)</dt>
        <dd><input type="text" name="source_details" id="f_source_details" value="{form_field name=source_details}" size="70" /></dd>
    </dl>
</fieldset>

<fieldset>
    <legend>Description du vélo</legend>
    <dl>
        <dt><label for="f_type">Type</label> <b>(obligatoire)</b></dt>
        <dd>{form_select name=type values=$types}</dd>
        <dt><label for="f_roues">Taille des roues</label></dt>
        <dd>{form_select name=roues values=$roues}</dd>
        <dt><label for="f_genre">Genre</label> <b>(obligatoire)</b></dt>
        <dd>{form_select name=genre values=$genres}</dd>
        <dt><label for="f_couleur">Couleur</label> <b>(obligatoire)</b></dt>
        <dd><input type="text" name="couleur" id="f_couleur" value="{form_field name=couleur}" size="50" required="required" /></dd>
        <dt><label for="f_modele">Marque et modèle</label> <b>(obligatoire)</b></dt>
        <dd><input type="text" name="modele" id="f_modele" value="{form_field name=modele}" size="50" required="required" /></dd>
    </dl>
</fieldset>

<fieldset>
    <legend>Entrée du vélo</legend>
    <dl>
        <dt><label for="f_date_entree">Date d'entrée dans le stock</label> <b>(obligatoire)</b></dt>
        <dd>
            <input type="date" name="date_entree" id="f_date_entree" value="{form_field name=date_entree default=$now_ymd}" />
        </dd>
        <dt><label for="f_etat_entree">État à l'entrée dans le stock</label> <b>(obligatoire)</b></dt>
        <dd><input type="text" name="etat_entree" id="f_etat_entree" value="{form_field name=etat_entree}" size="70" required="required" /></dd>
    </dl>
</fieldset>

<fieldset>
    <legend>Sortie du vélo</legend>
    <dl>
        <dt><label for="f_date_sortie">Date de sortie</label></dt>
        <dd>
            <input type="date" name="date_sortie" id="f_date_sortie" value="{form_field name=date_sortie}" />
        </dd>
        <dt><label for="f_raison_sortie">Raison de sortie</label></dt>
        <dd>{form_select name=raison_sortie values=$raisons_sortie}</dd>
        <dt><label for="f_details_sortie">Détails de sortie</label></dt>
        <dd>Inscrire le numéro d'adhérent en cas de vente.</dd>
        <dd><input type="text" name="details_sortie" id="f_details_sortie" value="{form_field name=details_sortie}" size="70" /></dd>
    </dl>
</fieldset>

<p class="submit">
    {csrf_field key="ajout_velo"}
    <input type="submit" name="save" value="Enregistrer &rarr;" />
</p>
</form>

{include file="admin/_foot.tpl"}