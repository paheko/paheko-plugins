{include file="_head.tpl" title="%s — Configurer"|args:$f.name}

{form_errors}

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Synchronisation avec la comptabilité</legend>
		<dl>
			{input type="select" options=$years_assoc name="id_year" source=$f required=false label="Exercice comptable" default_empty="— Ne pas synchroniser —"}
			<dd class="help">Si un exercice est sélectionné, les commandes passées avec cette campagne et ayant été payées seront transformées en écritures comptables selon la configuration des tarifs et options.</dd>
			{input type="list" target="!acc/charts/accounts/selector.php?types=6&key=code" name="payment_account_code" label="Compte de recette pour les paiements reçus" default=$payment_account}
		</dl>
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
	</p>
</form>

{include file="_foot.tpl"}
