{include file="admin/_head.tpl" title="Suivi du temps" plugin_css=['style.css']}

<nav class="tabs">
	<ul>
		<li><a href="./">Mon temps</a></li>
		<li><a href="stats.php">Statistiques</a></li>
		<li class="current"><a href="config.php">Configuration</a></li>
	</ul>
</nav>

<form method="post" action="">
	<fieldset>
		<legend>Modifier une tâche</legend>
		<dl>
			{input type="text" name="label" required=true label="Libellé" source=$task}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="edit" label="Modifier" shape="edit" class="main"}
	</p>
</form>


{include file="admin/_foot.tpl"}