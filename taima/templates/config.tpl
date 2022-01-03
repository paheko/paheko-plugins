{include file="admin/_head.tpl" title="Suivi du temps" plugin_css=['style.css']}

{include file="%s/templates/_nav.tpl"|args:$plugin_root current="config"}

<h2>Liste des tâches</h2>

<table class="list">
	<tbody>
	{foreach from=$tasks item="task"}
		<tr>
			<th>{$task.label}</th>
			<td class="actions">
				{linkbutton label="Éditer" href="?edit=%d"|args:$task.id shape="edit" target="_dialog"}
				{linkbutton label="Supprimer" href="?delete=%d"|args:$task.id shape="delete" target="_dialog"}
			</td>
		</tr>
	{/foreach}
	</tbody>
</table>

<form method="post" action="">
	<fieldset>
		<legend>Ajouter une tâche</legend>
		<dl>
			{input type="text" name="label" required=true label="Libellé"}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="add" label="Ajouter cette tâche à la liste" shape="plus" class="main"}
	</p>
</form>


{include file="admin/_foot.tpl"}