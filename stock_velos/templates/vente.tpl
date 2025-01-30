{include file="_head.tpl" title="Vendre un vélo"}

{include file="./_nav.tpl" current=""}

<section class="fiche">
    <nav>
        <ul class="sub_actions">
            <li class="fiche"><a href="fiche.php?id={$velo.id|escape}">Voir la fiche de ce vélo</a></li>
            <li class="modifier"><a href="modifier.php?id={$velo.id|escape}">Modifier la fiche de ce vélo</a></li>
        </ul>
    </nav>

    <article class="velo">
        <dl class="num">
            <dt>Numéro unique</dt>
            <dd>{$velo.id|escape}</dd>
        </dl>
        <dl class="etiq">
            <dt>Étiquette</dt>
            <dd>{$velo.etiquette|escape}</dd>
        </dl>
        {if $velo.prix > 0}
        <dl class="etiq">
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
        <legend>Informations sur la vente</legend>
        <dl>
            <dt><label for="f_prix">Prix</label></dt>
            <dd><input type="text" id="f_prix" name="prix" size="5" maxlength="3" value="{$prix|escape}" /> €</dd>
            <dt><label for="f_etat">État du vélo</label></dt>
            <dd><textarea name="etat" id="f_etat" cols="70" rows="5">{$etat}</textarea></dd>
            <dt><label for="f_adherent">Numéro de l'adhérent</label></dt>
            <dd><input type="number" id="f_adherent" name="adherent" size="5" required="required" /></dd>
        </dl>
    </fieldset>

    <p class="submit">
        {csrf_field key="vente_velo_%s"|args:$velo.id}
        <input type="submit" name="sell" value="Confirmer la vente" />
    </p>
    </form>

</section>

{include file="_foot.tpl"}