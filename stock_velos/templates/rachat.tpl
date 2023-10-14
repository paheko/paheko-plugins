{include file="_head.tpl" title="Racheter un vélo"}

{include file="./_nav.tpl" current=""}

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
                {input type="number" name="etiquette" required=true}
                <input type="button" onclick="document.getElementById('f_etiquette').value='{$libre|escape}';" value="Utiliser la première étiquette libre (n°{$libre|escape})" />
            </dd>

            {input type="number" label="Prix de rachat" required=true default=$prix name="prix"}
            {input type="textarea" name="etat" label="État du vélo"}

            <dt><label for="f_adherent">Adhérent</label></dt>
            <dd>N°{$velo.details_sortie|escape} — {$velo->membre_sortie()}</dd>
        </dl>
    </fieldset>

    <p class="submit">
        {csrf_field key="rachat_velo_%s"|args:$velo.id}
        <input type="submit" name="buy" value="Confirmer le rachat" />
    </p>
    </form>

</section>

{include file="_foot.tpl"}