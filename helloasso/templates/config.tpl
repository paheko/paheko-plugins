{include file="_head.tpl" title="Synchronisation des membres"}

{include file="./_menu.tpl" current="config" sub_current="users"}

{if $_GET.msg === 'SAVED'}
<p class="confirm block">
	Configuration enregistrée.
</p>
{/if}

{form_errors}

<form method="post" action="{$self_url}">

	<fieldset>
		<legend>Correspondance des membres</legend>
		<dl>
			{input type="select" options=$match_options name="match_email_field" source=$plugin_config required=true label="Champ utilisé pour savoir si un membre existe déjà" help="Si vous avez des membres homonymes, il est préférable d'utiliser le champ e-mail pour éviter les erreurs."}
		{if $name_field}
			{input type="select" name="merge_names_order" label="Ordre du nom et prénom" options=$merge_names_order_options required=true source=$plugin_config help="Indiquer ici si le champ '%s' de la fiche membre doit avoir le nom ou le prénom en premier."|args:$name_field.label}
		{/if}
		</dl>
	</fieldset>

	<fieldset>
		<legend>Correspondance des champs des fiches de membres</legend>
		<p class="help">Indiquer ici quelle information de HelloAsso doit correspondre un champ de la fiche membre.</p>
		<table class="list auto">
			<thead>
				<tr>
					<th scope="col">Information HelloAsso</th>
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

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
	</p>
</form>

{include file="_foot.tpl"}
