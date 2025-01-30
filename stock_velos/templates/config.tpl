{include file="_head.tpl" title="Configuration"}

{include file="./_nav.tpl" current="config"}

{form_errors}

<form method="post" action="">

{if isset($_GET.ok)}
    <p class="block confirm">
        La configuration a été enregistrée.
    </p>
{/if}

<fieldset>
	<legend>Informations de chaque vélo</legend>
	<p class="help">
		Indiquer ici quels champs doivent être utilisés lors de l'enregistrement d'un vélo.
	</p>
	<table class="list">
		<thead>
			<tr>
				<td>Activé</td>
				<td>Obligatoire</td>
				<th>Nom</th>
				<td></td>
			</tr>
		</thead>
		<tbody>
		{foreach from=$fields item="field"}
			<tr class="{if !$field.enabled}disabled{/if}">
				<td>{input type="checkbox" name="enabled[%s]"|args:$field.name value=1 default=$field.enabled}</td>
				<td>
					{if $field.can_require}
						{input type="checkbox" name="required[%s]"|args:$field.name value=1 default=$field.required}
					{/if}
				</td>
				<td>{$field.label}</td>
				<td class="actions">
					{if $field.has_options}
						{linkbutton shape="edit" href="config_options.php?field=%s"|args:$field.name label="Modifier les options" target="_dialog"}
					{/if}
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
</fieldset>

<p class="submit">
	{csrf_field key=$csrf_key}
	{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
</p>

</form>

{include file="_foot.tpl"}