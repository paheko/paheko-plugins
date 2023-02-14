{include file="_head.tpl" title="Configuration"}

{include file="./_menu.tpl" current="config"}

{if $_GET.ok !== null}
<p class="confirm block">
	Configuration enregistrée.
</p>
{/if}


{form_errors}

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Correspondance des membres</legend>
		<dl>
			{input type="select" options=$match_options name="match_email_field" source=$plugin_config required=true label="Champ utilisé pour savoir si un membre existe déjà"}
		</dl>
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
	</p>
</form>

{include file="_foot.tpl"}
