{include file="_head.tpl" title="Créer un client à partir d'une fiche de membre" current="plugin_invoice"}

{form_errors}

<form method="get" action="edit.php" data-focus="1">

<fieldset>
	<legend>Informations générales</legend>
	<dl>
		{input type="list" name="from_user" label="Membre" required=true target="!users/selector.php"}
	</dl>
</fieldset>

<p class="submit">
	{button type="submit" label="Continuer" shape="right" class="main"}
</p>

</form>

{include file="_foot.tpl"}