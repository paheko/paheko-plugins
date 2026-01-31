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
			{input type="number" label="Prix" name="prix" default=$prix}
			{input type="textarea" name="etat" label="État du vélo" cols=70 rows=5 default=$etat required=false}
			{if $fields.details_sortie.enabled}
			{input type="number" size=5 name="adherent" label="Numéro de l'adhérent" required=$fields.details_sortie.required}
			{/if}
		</dl>
	</fieldset>

	<p class="submit">
		{csrf_field key="vente_velo_%s"|args:$velo.id}
		<input type="submit" name="sell" value="Confirmer la vente" />
	</p>
	</form>

</section>

{include file="_foot.tpl"}