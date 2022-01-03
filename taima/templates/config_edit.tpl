{include file="admin/_head.tpl" title="Suivi du temps" plugin_css=['style.css']}

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