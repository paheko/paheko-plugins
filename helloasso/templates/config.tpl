{include file="_head.tpl" title="Configuration"}

<nav class="tabs">
	<ul>
		<li class="current"><a href="config.php?{$dialog_qs}">Configuration</a></li>
		<li><a href="config_client.php?{$dialog_qs}">Connexion à HelloAsso</a></li>
	</ul>
</nav>

{form_errors}

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Correspondance des membres</legend>
		<dl>
			{input type="select" options=$match_options name="match_email_field" source=$plugin_config required=true label="Champ utilisé pour savoir si un membre existe déjà"}
		{if $name_field}
			{input type="select" name="merge_names_order" label="Ordre des champs nom et prénom dans le champ '%s'"|args:$name_field.label options=$merge_names_order_options required=true source=$plugin_config}
		{/if}
		</dl>
	</fieldset>

	<fieldset>
		<legend>Correspondance des champs des fiches de membres</legend>
		<p class="help">Indiquer ici à quel champ de la fiche membre les données fournies par HelloaAsso doivent correspondre.</p>
		<table class="list auto">
			<thead>
				<tr>
					<th scope="col">HelloAsso</th>
					<th scope="col">Fiche membre</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$ha_fields key="key" item="label"}
				<?php $selected = $plugin_config->fields_map->$key ?? null; ?>
				<tr>
					<th scope="row">{$label}</th>
					<td>{input type="select" name="fields_map[%s]"|args:$key options=$fields_assoc default_empty="— Ne pas utiliser —" default=$selected}</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	</fieldset>

	<fieldset>
		<legend>Comptabilité</legend>
		<dl>
			{input type="list" target="!acc/charts/accounts/selector.php?types=1&key=code" name="provider_account_code" label="Compte de HelloAsso" default=$provider_account help="HelloAsso étant un établissement bancaire ou assimilé, généralement on crée un compte 512, par exemple '512HA' pour les paiements qui sont reçus par ce prestataire, en attendant qu'il les reverse sur le vrai compte bancaire de l'association."}
			{input type="list" target="!acc/charts/accounts/selector.php?types=1&key=code" name="bank_account_code" label="Compte de banque des versements" default=$bank_account help="Sélectionner ici le compte bancaire qui reçoit les versements effectués par HelloAsso."}
			{input type="list" target="!acc/charts/accounts/selector.php?types=6&key=code" name="donation_account_code" label="Compte de recette pour les dons reçus" default=$donation_account}
		</dl>
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
	</p>
</form>

{include file="_foot.tpl"}
