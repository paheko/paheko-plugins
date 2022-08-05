{include file="admin/_head.tpl" title="Suivi du temps" plugin_css=['style.css']}

{form_errors}

<form method="post" action="" data-focus="1">
	<fieldset>
		<legend>Modifier une tâche</legend>
		<dl>
			{input type="text" name="label" required=true label="Libellé" source=$task}
			{input type="text" name="account" label="Code du compte d'emploi" required=false help="Compte qui sera utilisé pour reporter l'emploi du temps bénévole dans le bilan comptable. Généralement c'est le compte 864." source=$task}
			{input type="money" name="value" required=false label="Valorisation d'une heure" help="Inscrire ici la valeur d'une heure de temps pour le bilan comptable" source=$task}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="edit" label="Modifier" shape="edit" class="main"}
	</p>
</form>


{include file="admin/_foot.tpl"}