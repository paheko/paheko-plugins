{include file="_head.tpl" title="%s — Configurer le tarif"|args:$tier.label}

{include file="./_menu.tpl" current="home" current_sub="config"}

{form_errors}

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Synchronisation avec la comptabilité</legend>
		<dl>
			{input type="list" target="!acc/charts/accounts/selector.php?types=6&key=code" name="account_code" label="Compte de recette" default=$account can_delete=true}
			<dd class="help">Laisser vide pour utiliser le compte défini pour la campagne.</dd>
		</dl>
	</fieldset>

	{if $f.type === 'Membership'}
		<fieldset>
			<legend>Liaison ou création automatisée de membre</legend>
			<p class="help">Indiquer ici si vous souhaitez lier et créer des fiches de membre pour chaque personne mentionnée dans l'adhésion HelloAsso. La recherche se fait sur la combinaison nom et prénom. Si vous avez des membres homonymes, il est possible que l'adhésion se retrouve associée au mauvais membre.</p>
			<dl>
				{input type="radio-btn" name="create_user" value=0 source=$tier label="Ne pas chercher à lier aux membres" prefix_title="Liaison avec les membres" prefix_required=true required=true help="Dans ce cas vous pourrez toujours lier ou créer un membre manuellement depuis l'adhésion."}
				{input type="radio-btn" name="create_user" value=1 source=$tier label="Chercher à lier un membre existant, s'il n'existe pas créer un nouveau membre"}
				{input type="radio-btn" name="create_user" value=2 source=$tier label="Seulement chercher à lier un membre existant, s'il n'existe pas aucun membre ne sera créé"}
			</dl>
		</fieldset>

		{if !empty($ha_fields)}
		<fieldset>
			<legend>Correspondance des champs des fiches de membres</legend>
			<p class="help">Indiquer ici quelle information de HelloAsso doit correspondre un champ de la fiche membre.</p>
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

		<fieldset>
			<legend>Inscription à une activité</legend>
			<dl>
				{input type="select_groups" name="id_fee" default_empty="— Ne pas inscrire —" label="Tarif auquel inscrire le membre" options=$fees source=$tier}
				<dd class="help">Si un tarif est sélectionné, le membre lié (ou créé) sera inscrit à ce tarif. Si aucun membre n'est trouvé, aucune inscription ne sera enregistrée.</dd>
			</dl>
		</fieldset>
	{/if}

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
	</p>
</form>

{include file="_foot.tpl"}
