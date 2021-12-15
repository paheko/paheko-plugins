{include file="admin/_head.tpl" title="Gestion catégorie" current="plugin_%s"|args:$plugin.id}

{form_errors}

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Modifier une catégorie</legend>
		<dl>
			{input type="text" name="name" label="Nom" required=true source=$cat}
			{input type="text" name="account" label="Code du compte" source=$cat help="Code du compte dans le plan comptable (par exemple 754), utilisé pour intégrer les notes à la comptabilité."}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
	</p>
</form>

{include file="admin/_foot.tpl"}