{include file="_head.tpl" title="Lieu de vente"}

{include file="../_nav.tpl" current="config" subcurrent="locations"}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1">
	<fieldset>
		<legend>{if $location->exists()}Modifier un lieu de vente{else}Nouveau lieu de vente{/if}</legend>
		<dl>
			{input type="text" name="name" label="Nom" required=true source=$location}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
	</p>
</form>

{include file="_foot.tpl"}