{include file="_head.tpl" title="Lier une écriture"}

{form_errors}
<form method="post" action="">

<fieldset>
	<legend>Liée à une écriture comptable existante</legend>
	<dl>
		{input type="text" pattern="\d+" label="Numéro de l'écriture" name="id_transaction" required=true}
	</dl>
</fieldset>

<p class="submit">
	{csrf_field key=$csrf_key}
	{button shape="right" label="Lier à l'écriture comptable" name="link" type="submit" class="main"}
</p>
</form>

{include file="_foot.tpl"}
