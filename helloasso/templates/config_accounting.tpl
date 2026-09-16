{include file="_head.tpl" title="Synchronisation de la comptabilité"}

{include file="./_menu.tpl" current="config" sub_current="accounting"}

{form_errors}

<form method="post" action="{$self_url}">

	<fieldset>
		<legend>Comptabilité</legend>
		<?php $enabled = isset($provider_account); ?>
		<dl>
			{input type="radio-btn" name="enabled" value=1 default=$enabled label="Activer la synchronisation avec la comptabilité"}
			{input type="radio-btn" name="enabled" value=0 default=$enabled label="Désactiver la synchronisation avec la comptabilité"}
		</dl>
	</fieldset>

	<fieldset class="acc-enabled">
		<legend>Comptes à utiliser</legend>
		<dl>
			{input type="list" target="!acc/charts/accounts/selector.php?types=1&key=code" name="provider_account_code" label="Compte de HelloAsso" default=$provider_account help="HelloAsso étant un établissement bancaire ou assimilé, il doit avoir un compte 512, par exemple '512HA' pour les paiements qui sont reçus par ce prestataire. Quand HelloAsso reverse les fonds il suffit alors d'enregistrer un virement vers le vrai compte bancaire de l'association."  required=true}
			{* TODO: for later
			{input type="list" target="!acc/charts/accounts/selector.php?types=1&key=code" name="bank_account_code" label="Compte de banque des versements" default=$bank_account help="Sélectionner ici le compte bancaire qui reçoit les versements effectués par HelloAsso." can_delete=true}
			*}
			{input type="list" target="!acc/charts/accounts/selector.php?types=6&key=code" name="donation_account_code" label="Compte de recette pour les dons" default=$donation_account required=true help="Généralement il s'agit du compte « 754 — Ressources liées à la générosité du public »"}
			{input type="list" target="!acc/charts/accounts/selector.php?types=6&key=code" name="payment_account_code" label="Compte de recette par défaut pour les autres types de recettes" default=$payment_account required=true help="Il sera possible de spécifier un compte différent pour chaque campagne et option, mais si aucun compte n'est spécifié pour l'option et la campagne, c'est celui-ci qui sera utilisé."}
		</dl>
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
	</p>
</form>

<script type="text/javascript">
{literal}
function toggleAccounting() {
	g.toggle('.acc-enabled', document.forms[0].elements['enabled'].value == 1);
}
toggleAccounting();
$('input[name="enabled"]').forEach(i => i.onchange = toggleAccounting);
{/literal}
</script>

{include file="_foot.tpl"}
