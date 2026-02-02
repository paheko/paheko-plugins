{include file="_head.tpl" title="%s — Configurer l'option"|args:$option.label}

{include file="./_menu.tpl" current="home" current_sub="config"}

{form_errors}

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Synchronisation avec la comptabilité</legend>
		<dl>
			{input type="list" target="!acc/charts/accounts/selector.php?types=6&key=code" name="account_code" label="Compte de recette" default=$account}
			<dd class="help">Si un compte est sélectionné, une écriture sera créée pour chaque paiement correspondant à cette option. Laisser vide pour utiliser le compte de recette indiqué pour la campagne.</dd>
		</dl>
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
	</p>
</form>

{include file="_foot.tpl"}
