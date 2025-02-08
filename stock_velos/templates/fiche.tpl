{include file="_head.tpl" title="Vélo n°%s"|args:$velo.id}

{include file="./_nav.tpl" current=""}

<section class="fiche">
	<p>
		{linkbutton shape="edit" href="modifier.php?id=%d"|args:$velo.id label="Modifier la fiche de ce vélo"}
		{linkbutton shape="delete" href="delete.php?id=%d"|args:$velo.id label="Supprimer"}
		{if empty($velo.date_sortie) && $velo.prix > 0}
			{linkbutton shape="money" href="vente.php?id=%d"|args:$velo.id label="Vendre ce vélo"}
		{elseif empty($velo.date_sortie) && $velo.prix == 0}
			{linkbutton shape="money" href="vente.php?id=%d&prix=20&etat=Pour%%20pièces"|args:$velo.id label="Vendre pour pièces"}
		{elseif $velo.prix > 0}
			{linkbutton shape="print" href="vente_ok.php?id=%d"|args:$velo.id label="Ré-imprimer contrat de vente"}
			{if empty($rachat)}
				{linkbutton shape="money" href="rachat.php?id=%d"|args:$velo.id label="Racheter ce vélo"}
			{/if}
		{/if}
	</p>

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
		{if $velo.etiquette}
		<dl class="etiq">
			<dt>Étiquette</dt>
			<dd>{$velo.etiquette|escape}</dd>
		</dl>
		{/if}
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
		<form method="post" action="https://apic-asso.com/wp-admin/admin-ajax.php" target="_blank" data-disable-progress="1">
			<input type="hidden" name="action" value="ajax_get_bike_status" />
			<input type="hidden" name="bike_id" value="{$velo.bicycode}" />
			{button type="submit" label="Vérifier le numéro Bicycode"}
		</form>
		<dl>
			<dt>Marquage Bicycode</dt>
			<dd>
				{$velo.bicycode}
			</dd>
		</dl>
		</form>
		{/if}
		<dl>
			<dt>Type</dt>
			<dd>{$velo.type|escape}</dd>
		</dl>
		<dl>
			<dt>Taille</dt>
			<dd>{$velo.roues|escape}</dd>
		</dl>
		<dl>
			<dt>Genre de cadre</dt>
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
		<dl>
			<dt>Poids</dt>
			<dd>{if $velo.poids}{$velo.poids|weight:true} kg{else}<i>Non précisé</i>{/if}</dd>
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
			<dd>Le {$velo.date_entree|date_short}</dd>
			<dd>État : {$velo.etat_entree|escape}</dd>
			<dt>Provenance</dt>
			<dd>{$velo.source|escape} &mdash;
				{if $velo.source == 'Don' && is_numeric($velo.source_details)}
					<a href="{$admin_url}users/details.php?number={$velo.source_details|escape}">Membre n°{$velo.source_details|escape} — {$velo->membre_source()}</a>
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
			<dd>Le {$velo.date_sortie|date_short}</dd>
			<dd>Motif de sortie :
				{$velo.raison_sortie|escape} &mdash;
				{if $velo.raison_sortie == 'Vendu' && is_numeric($velo.details_sortie)}
					<a href="{$admin_url}users/details.php?number={$velo.details_sortie|escape}">Membre n°{$velo.details_sortie|escape} — {$velo->membre_sortie()}</a>
				{else}
					{$velo.details_sortie|escape}
				{/if}
			</dd>
		</dl>
	</article>
	{/if}

	{if $rachat = $velo->get_buyback()}
	<article class="velo_sortie">
		<dl>
			<dt>Vélo racheté</dt>
			<dd>Nouveau numéro : <a href="fiche.php?id={$rachat}">{$rachat}</a></dd>
		</dl>
	</article>
	{/if}


	<article class="velo_desc attachments noprint">
		{include file="common/files/_context_list.tpl" edit=true path="%s/public/%d"|args:$plugin->storage_root():$velo.id button_label="Ajouter une photo du vélo"}
	</article>

</section>

{include file="_foot.tpl"}