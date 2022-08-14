{include file="_head.tpl" title="Racheter un vélo" current="plugin_`$plugin.id`"}

{include file="%s_nav.tpl"|args:$plugin_tpl current=""}

<section class="fiche">
    <ul class="sub_actions">
        <li class="fiche"><a href="fiche.php?id={$velo.id|escape}">Voir la fiche de ce vélo</a></li>
    </ul>

    <article class="velo">
        <dl class="num">
            <dt>Numéro unique</dt>
            <dd>{$velo.id|escape}</dd>
        </dl>
        <dl class="num">
            <dt>Numéro étiquette</dt>
            <dd>{$velo.etiquette|escape}</dd>
        </dl>
        {if $velo.prix > 0}
        <dl class="num">
            <dt>Prix</dt>
            <dd>{$velo.prix|escape} €</dd>
        </dl>
        {elseif $velo.prix < 0}
        <dl class="etat demonter">
            <dt>À démonter</dt>
        </dl>
        {else}
        <dl class="etat stock">
            <dt>Divers stock</dt>
        </dl>
        {/if}
    </article>


    <article class="velo_desc">
        <dl>
            <dt>Type</dt>
            <dd>{$velo.type|escape}</dd>
        </dl>
        <dl>
            <dt>Roues</dt>
            <dd>{$velo.roues|escape}</dd>
        </dl>
        <dl>
            <dt>Genre</dt>
            <dd>{$velo.genre|escape}</dd>
        </dl>
        <dl>
            <dt>Couleur</dt>
            <dd>{$velo.couleur|escape}</dd>
        </dl>
        <dl>
            <dt>Marque et modèle</dt>
            <dd>{$velo.modele|escape}</dd>
        </dl>
    </article>

    {form_errors}

    <form method="post" action="">
    <fieldset>
        <legend>Informations sur le rachat</legend>
        <dl>
            <dt><label for="f_etiquette">Nouveau numéro étiquette</label> <b>(obligatoire)</b></dt>
            <dd>
                <input type="number" name="etiquette" id="f_etiquette" value="{form_field name=etiquette}" required="required" />
                <input type="button" onclick="document.getElementById('f_etiquette').value='{$libre|escape}';" value="Utiliser la première étiquette libre (n°{$libre|escape})" />
            </dd>

            <dt><label for="f_prix">Prix de rachat</label></dt>
            <dd><input type="text" id="f_prix" name="prix" size="5" maxlength="3" value="{$prix|escape}" /> €</dd>
            <dt><label for="f_etat">État du vélo</label></dt>
            <dd><textarea name="etat" id="f_etat" cols="70" rows="5"></textarea></dd>
            <dt><label for="f_adherent">Adhérent</label></dt>
            <dd>N°{$velo.details_sortie|escape} — {$adherent.identite|escape}</dd>
        </dl>
    </fieldset>

    <p class="submit">
        {csrf_field key="rachat_velo_%s"|args:$velo.id}
        <input type="submit" name="buy" value="Confirmer le rachat" />
    </p>
    </form>

</section>

{include file="_foot.tpl"}