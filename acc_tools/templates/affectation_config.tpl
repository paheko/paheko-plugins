{include file="_head.tpl" title="Règles d'affectation"}

{form_errors}

<form method="post" action="">
	<fieldset>
		<legend>Règles d'affectation</legend>
		<table class="list">
			<thead>
				<tr>
					<td>Si le libellé contient…</td>
					<td>Seulement si l'écriture est…</td>
					<td>Numéro compte de débit</td>
					<td>Numéro compte de crédit</td>
					<td>Changer le libellé pour… <em>(facultatif)</em></td>
					<td></td>
				</tr>
			</thead>
			<tbody>
			{foreach from=$rules item="rule"}
				<tr>
					<td>{input type="text" name="rules[match][]" default=$rule.match}</td>
					<td>{input type="select" name="rules[only_if][]" options=$only_options default=$rule.only_if}</td>
					<td>{input type="text" size=6 name="rules[debit][]" default=$rule.debit}</td>
					<td>{input type="text" size=6 name="rules[credit][]" default=$rule.credit}</td>
					<td>{input type="text" name="rules[new_label][]" default=$rule.new_label}</td>
					<td class="actions">{button shape="minus" label="Supprimer" onclick="this.parentNode.parentNode.remove()"}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
		<p class="actions">
			{button type="button" label="Ajouter une ligne" shape="plus" onclick="addLine();"}
			{button type="submit" name="save" label="Enregistrer" shape="right"}
			{csrf_field key=$csrf_key}
		</p>
	</fieldset>
</form>

<script type="text/javascript">
{literal}
function addLine() {
	var row = document.querySelector('tbody tr').cloneNode(true);

	row.querySelectorAll('input, select').forEach((i) => i.value = '');
	document.querySelector('tbody').appendChild(row);
	g.resizeParentDialog();
}
{/literal}
</script>

{include file="_foot.tpl"}
