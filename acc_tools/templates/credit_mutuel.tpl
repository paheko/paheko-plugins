{include file="_head.tpl" title="Conversion Crédit Mutuel"}

{form_errors}

<p>{linkbutton href="./" label="Retour" shape="left"}</p>

<form method="post" action="" enctype="multipart/form-data">
	<fieldset>
		<legend>Charger un extrait de compte Crédit Mutuel PDF</legend>
		<dl>
			{input type="file" name="file[]" required=true label="Fichier PDF" accept=".pdf,application/pdf,.PDF" multiple=true}
		</dl>
		<p class="submit">
			{csrf_field key=$csrf_key}
			{button type="submit" label="Charger" shape="right" class="main" name="load"}
		</p>
	</fieldset>
</form>

{include file="_foot.tpl"}
