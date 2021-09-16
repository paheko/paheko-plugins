{include file="admin/_head.tpl" title="Suivi du temps" plugin_css=['style.css']}

<nav class="tabs">
	<ul>
		<li><a href="./">Mon temps</a></li>
		<li><a href="stats.php">Statistiques</a></li>
		<li class="current"><a href="config.php">Configuration</a></li>
	</ul>
</nav>

<h2>Liste des tâches</h2>

<table class="list">
	<tbody>
	{foreach from=$tasks item="task"}
		<tr>
			<th>{$task.label}</th>
			<td class="actions">
				{linkbutton label="Éditer" href="?edit=%d"|args:$task.id shape="edit"}
				{linkbutton label="Supprimer" href="?delete=%d"|args:$task.id shape="delete"}
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