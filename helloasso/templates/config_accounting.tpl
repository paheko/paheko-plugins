{include file="_head.tpl" title="Synchronisation de la comptabilité"}

{include file="./_menu.tpl" current="config" sub_current="accounting"}

{form_errors}

<form method="post" action="{$self_url}">

	<fieldset>
		<legend>Comptabilité</legend>
		<dl>
			{input type="list" target="!acc/charts/accounts/selector.php?types=1&key=code" name="provider_account_code" label="Compte de HelloAsso" default=$provider_account help="HelloAsso étant un établissement bancaire ou assimilé, généralement on crée un compte 512, par exemple '512HA' pour les paiements qui sont reçus par ce prestataire, en attendant qu'il les reverse sur le vrai compte bancaire de l'association." can_delete=true}
			{* FIXME: for later
			{input type="list" target="!acc/charts/accounts/selector.php?types=1&key=code" name="bank_account_code" label="Compte de banque des versements" default=$bank_account help="Sélectionner ici le compte bancaire qui reçoit les versements effectués par HelloAsso." can_delete=true}
			*}
			{input type="list" target="!acc/charts/accounts/selector.php?types=6&key=code" name="donation_account_code" label="Compte de recette pour les dons" default=$donation_account can_delete=true}
			{input type="list" target="!acc/charts/accounts/selector.php?types=6&key=code" name="payment_account_code" label="Compte de recette pour les autres paiements" default=$payment_account can_delete=true}
		</dl>
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
	</p>
</form>

{include file="_foot.tpl"}
