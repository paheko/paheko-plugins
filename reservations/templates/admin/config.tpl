{include file="admin/_head.tpl" title="Configuration — %s"|args:$plugin.nom current="plugin_%s"|args:$plugin.id js=1}

{include file="%s/templates/admin/_menu.tpl"|args:$plugin_root current="config"}

{form_errors}

{if $ok && !$form->hasErrors()}
	<p class="confirm">
		La configuration a bien été enregistrée.
	</p>
{/if}

<table class="list">
	<thead>
		<tr>
			<th>Nom</th>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$categories item="cat"}
		<tr>
			<th>{$cat.nom}</th>
			<td class="actions">
	            <a class="icn" href="config_cat.php?id={$cat.id}" title="Modifier">✎</a>
	            <a class="icn" href="config_slots.php?id={$cat.id}" title="Configurer les créneaux">𝍢</a>
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>

<form method="post" action="{$self_url}">

	<fieldset>
		<legend>Ajouter un type de créneau</legend>
		<p class="help">
			Si vous ne définissez qu'un seul type de créneaux, la personne faisant une réservation ne verra que le texte de présentation et les créneaux associés.
		</p>
		<p class="help">
			Si vous définissez plusieurs types de créneaux, elle devra d'abord choisir un type de créneau, et sera verra présentée la liste des types de créneaux avec leurs noms et textes d'introduction.
		</p>
		<dl>
			<dt><label for="f_nom">Nom</label></dt>
			<dd><input type="text" name="nom" id="f_nom" value="{form_field name="nom"}" required="required" /></dd>
		</dl>
		<p class="submit">
			{csrf_field key="config_plugin_%s"|args:$plugin.id}
			<input type="submit" name="add" value="Ajouter" />
		</p>
	</fieldset>

</form>



<div class="help">
	<h3>Aide</h3>
	<p class="help">Les membres connectés peuvent réserver un créneau via l'onglet « Réservations » du menu de gauche.</p>
	<p class="help">Les non-membres peuvent réserver un créneau via l'adresse suivante :<br />
		<a href="{$www_url}p/{$plugin.id}/" target="_blank">{$www_url}p/{$plugin.id}/</a></p>
	<p class="help">Les administrateurs peuvent visionner les réservations et gérer les inscrit⋅e⋅s dans l'onglet « Réservations ».</p>
</div>

{include file="admin/_foot.tpl"}
