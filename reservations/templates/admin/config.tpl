{include file="admin/_head.tpl" title="Configuration — %s"|args:$plugin.nom current="plugin_%s"|args:$plugin.id}

{form_errors}

{if $ok && !$form->hasErrors()}
	<p class="confirm">
		La configuration a bien été enregistrée.
	</p>
{/if}

<form method="post" action="{$self_url}">

	<fieldset>
		<legend>Configuration</legend>
		<dl>
			<dt><label for="f_text">Texte à afficher sur la page de réservation</label></dt>
			<dd class="help">Syntaxe <a href="{$admin_url}wiki/_syntaxe.html" target="_blank">SkrivML</a> acceptée</dd>
			<dd><textarea name="text" id="f_text" cols="70" rows="15">{form_field name="text" data=$config}</textarea></dd>
		</dl>
	</fieldset>

	<fieldset>
		<legend>Créneaux</legend>
		<table class="list">
			<thead>
				<tr>
					<th>À partir du</th>
					<td>Heure</td>
					<td>Jauge maximale</td>
					<td></td>
					<td></td>
				</tr>
			</thead>
			<tbody>
				{foreach from=$slots item="slot"}
				<tr>
					<th><input type="date" name="slot[{$slot.id}][jour]" value="{$slot.jour}" required="required" /></th>
					<td><input type="time" name="slot[{$slot.id}][heure]" value="{$slot.heure}" required="required" /></td>
					<td><input type="number" name="slot[{$slot.id}][maximum]" value="{$slot.maximum}" required="required" /> personnes</td>
					<td><label><input type="checkbox" name="slot[{$slot.id}][repetition]" value="1" {if $slot.repetition}checked="checked"{/if} /> Répétition hebdomadaire</label></td>
					<td class="actions"><a href="#unsupported" onclick="return removeRow(this);" class="icn" title="Supprimer cette ligne">➖</a></td>
				</tr>
				{/foreach}
			</tbody>
		</table>
		<p class="actions"><a href="#unsupported" onclick="return addRow(this);" class="icn" title="Ajouter un créneau">➕</a></p>
	</fieldset>

	<p class="submit">
		{csrf_field key="config_plugin_%s"|args:$plugin.id}
		<input type="submit" name="save" value="Enregistrer &rarr;" />
	</p>

	<div class="help">
		<h3>Aide</h3>
		<p class="help">Les membres peuvent réserver un créneau via l'onglet « Réservations » du menu de gauche.</p>
		<p class="help">Les non-membres peuvent réserver un créneau via l'adresse suivante :<br />
			<input type="text" readonly="readonly" value="{$admin_url}p/{$plugin.id}/" /><input type="button" id="copyBtn" value="Copier dans le presse-papier" /></p>
		<p class="help">Les administrateurs peuvent visionner les créneaux et personnes inscrites dans l'onglet « Réservations ».</p>
	</div>

</form>


<script type="text/javascript">
{literal}
var index = 0;
function removeRow(e) {
	var row = e.parentNode.parentNode;
	var table = row.parentNode.parentNode;

	if (table.rows.length == 1)
	{
		return false;
	}

	row.parentNode.removeChild(row);
	return false;
}
function addRow(e) {
	var table = e.parentNode.parentNode.querySelector('table');
	var row = table.rows[table.rows.length-1];
	var new_row = row.cloneNode(true);
	row.parentNode.appendChild(new_row);

	index++;
	console.log(index);

	new_row.querySelectorAll('input').forEach(function (elm) {
		elm.name = elm.name.replace(/slot\[_?\d+\]/, 'slot[_' + index + ']');
	});
	return false;
}

document.getElementById('copyBtn').onclick = function (e) {
	var input = e.target.parentNode.querySelector('input');
	input.select();
	input.focus();
	input.setSelectionRange(0, 99999);
	document.execCommand("copy");
}
{/literal}
</script>

{include file="admin/_foot.tpl"}
