{include file="_head.tpl" title="%s — Configurer le tarif"|args:$tier.label}

{include file="./_menu.tpl" current="home" current_sub="config"}

{form_errors}

<form method="post" action="{$self_url}">
	{if $f.type === 'Membership'}
		<fieldset>
			<legend>Inscription à une activité</legend>
			<dl>
				{input type="select_groups" name="id_fee" default_empty="— Ne pas inscrire —" label="Tarif auquel inscrire le membre" options=$fees source=$tier}
				<dd class="help">Si un tarif est sélectionné, le membre lié (ou créé) sera inscrit à ce tarif. Si aucun membre n'est trouvé, aucune inscription ne sera enregistrée.</dd>
			</dl>
		</fieldset>
	{/if}

	<fieldset>
		<legend>Synchronisation avec la comptabilité</legend>
		<dl>
			{input type="list" target="!acc/charts/accounts/selector.php?types=6&key=code" name="account_code" label="Compte de recette" default=$account}
			<dd class="help">Si un compte est sélectionné, une écriture sera créée pour chaque paiement correspondant à ce tarif. Laisser vide pour ne pas synchroniser avec la comptabilité.</dd>
		</dl>
	</fieldset>

	<fieldset>
		<legend>Liaison ou création de membre</legend>
		<dl>
			{input type="radio-btn" name="create_user" value=0 source=$tier label="Ne pas chercher à lier aux membres" prefix_title="Liaison avec les membres" prefix_required=true required=true}
			{input type="radio-btn" name="create_user" value=1 source=$tier label="Chercher à lier un membre existant, sinon créer un nouveau membre"}
			{input type="radio-btn" name="create_user" value=2 source=$tier label="Seulement chercher à lier un membre existant, sinon ne rien faire"}
		</dl>
	</fieldset>

	{if count($ha_fields)}
	<fieldset>
		<legend>Correspondance des champs des fiches de membres</legend>
		<p class="help">Indiquer ici à quels champs de la fiche membre les informations fournies par HelloaAsso doivent correspondre.</p>
		<table class="list auto">
			<thead>
				<tr>
					<th scope="col">Information HelloAsso</th>
					<th scope="col">Champ de la fiche de membre</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$ha_fields key="key" item="label"}
				<?php $selected = $tier->fields_map[$label] ?? null; ?>
				<tr>
					<th scope="row">{$label}</th>
					<td>{input type="select" name="fields_map[%s]"|args:$label options=$fields_assoc default_empty="— Ne pas utiliser —" default=$selected}</td>
				</tr>
				{/foreach}
			</tbody>
			<p class="help">Note : le nom et le prénom sont toujours automatiquement associés selon la configuration de l'extension.</p>
		</table>
	</fieldset>
	{/if}

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
	</p>
</form>

{include file="_foot.tpl"}
