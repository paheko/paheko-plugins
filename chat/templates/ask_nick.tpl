{include file="_head.tpl" title="Choisir un pseudo" layout="public"}
{form_errors}

<form method="post" action="" data-focus="1">
	<fieldset>
		<legend>Choisir un pseudo</legend>
		<p class="help">Merci d'indiquer votre pseudo pour rejoindre la discussion.</p>
		<dl>
			{input type="text" label="Pseudo" required=true name="name" maxlength=16 minlength=2 pattern="[a-zA-Z][a-zA-Z0-9_]{1,15}" help="Seules les lettres, chiffres et tirets sont autorisés." oninput="this.value = this.value.replace(/[^a-zA-Z0-9_]+/ug, '_').toLowerCase();"}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="save" label="Connexion" class="main" shape="right"}
	</p>
</form>


{include file="_foot.tpl"}