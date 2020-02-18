{include file="admin/_head.tpl" title="Vélo n°%s"|args:$velo.id current="plugin_%s"|args:$plugin.id}

{include file="%s_nav.tpl"|args:$plugin_tpl current=""}

<section class="fiche">
    <ul class="sub_actions">
        <li class="modifier"><a href="{plugin_url file="modifier.php" query=1}id={$velo.id|escape}">Modifier la fiche de ce vélo</a></li>
        {if empty($velo.date_sortie) && $velo.prix > 0}
            <li class="vente"><a href="{plugin_url file="vente.php" query=1}id={$velo.id|escape}">Vendre ce vélo</a></li>
        {elseif empty($velo.date_sortie) && $velo.prix == 0}
            <li class="vente"><a href="{plugin_url file="vente.php" query=1}id={$velo.id}&amp;prix=20&amp;etat=Pour%20pièces">Vendre pour pièces</a></li>
        {elseif $velo.prix > 0}
            <li class="vente"><a href="{plugin_url file="vente_ok.php" query=1}id={$velo.id|escape}">Ré-imprimer contrat de vente</a></li>
            {if empty($rachat)}
                <li class="rachat"><a href="{plugin_url file="rachat.php" query=1}id={$velo.id|escape}">Racheter ce vélo</a></li>
            {/if}
        {/if}
    </ul>

    <article class="velo">
        <dl class="num">
            <dt>Numéro unique</dt>
            <dd>{$velo.id|escape}</dd>
        </dl>
        {if empty($velo.date_sortie)}
        <dl class="etat stock">
            <dt>En stock</dt>
        </dl>
        {else}
        <dl class="etat sortie">
            <dt>Sorti</dt>
        </dl>
        {/if}
        <dl class="etiq">
            <dt>Étiquette</dt>
            <dd>{$velo.etiquette|escape}</dd>
        </dl>
        {if $velo.prix > 0}
        <dl class="etiq">
            <dt>Prix</dt>
            <dd>{$velo.prix|escape}&nbsp;€</dd>
        </dl>
        {elseif $velo.prix < 0}
        <dl class="etat demonter">
            <dt>À démonter</dt>
        </dl>
        {else}
        {/if}
    </article>

    <article class="velo_desc">
        {if !empty($velo.bicycode)}
        <dl>
            <dt>Marquage Bicycode</dt>
            <dd><a href="http://bicycode.org/verification_vol.php?numero={$velo.bicycode|escape}">{$velo.bicycode|escape}</a></dd>
        </dl>
        {/if}
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

    {if !empty($velo.notes)}
    <article class="velo_desc">
        <p>
            <strong>Notes :</strong><br />
            {$velo.notes|escape|nl2br}
        </p>
    </article>
    {/if}

    <article class="velo_entree">
        <dl>
            <dt>Entrée du vélo</dt>
            <dd>Le {$velo.date_entree|format_sqlite_date_to_french}</dd>
            <dd>État : {$velo.etat_entree|escape}</dd>
            <dt>Provenance</dt>
            <dd>{$velo.source|escape} &mdash;
                {if $velo.source == 'Don' && is_numeric($velo.source_details)}
                    <a href="{$admin_url}membres/fiche.php?id={$velo.source_details|escape}">Membre n°{$velo.source_details|escape} — {$source_membre.identite|escape}</a>
                {elseif $velo.source == 'Rachat'}
                    Racheté (ancienne référence : <a href="fiche.php?id={$velo.source_details|escape}">{$velo.source_details|escape}</a>)
                {else}
                    {$velo.source_details|escape}
                {/if}
            </dd>
        </dl>
    </article>

    {if !empty($velo.date_sortie)}
    <article class="velo_sortie">
        <dl>
            <dt>Sortie du vélo</dt>
            <dd>Le {$velo.date_sortie|format_sqlite_date_to_french}</dd>
            <dd>Raison de sortie :
                {$velo.raison_sortie|escape} &mdash;
                {if $velo.raison_sortie == 'Vendu' && is_numeric($velo.details_sortie)}
                    <a href="{$admin_url}membres/fiche.php?id={$velo.details_sortie|escape}">Membre n°{$velo.details_sortie|escape} — {$sortie_membre.identite|escape}</a>
                {else}
                    {$velo.details_sortie|escape}
                {/if}
            </dd>
        </dl>
    </article>
    {/if}

    {if !empty($rachat)}
    <article class="velo_sortie">
        <dl>
            <dt>Vélo racheté</dt>
            <dd>Nouveau numéro : <a href="fiche.php?id={$rachat|escape}">{$rachat|escape}</a></dd>
        </dl>
    </article>
    {/if}
</section>

{include file="admin/_foot.tpl"}