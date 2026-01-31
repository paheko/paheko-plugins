{include file="_head.tpl" title="Gestion catégorie"}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1">
	<fieldset>
		<legend>Modifier une catégorie</legend>
		<dl>
			{input type="text" name="name" label="Nom" required=true source=$cat}
			{input required=true name="account" multiple=false target="!acc/charts/accounts/selector.php?key=code" type="list" label="Compte du plan comptable" default=$account help="Numéro du compte dans le plan comptable (par exemple 754), utilisé pour intégrer les notes à la comptabilité."}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
	</p>
</form>

{include file="_foot.tpl"}