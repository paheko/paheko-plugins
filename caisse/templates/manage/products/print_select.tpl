{include file="_head.tpl" title="Impression fiche produits"}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1">
	<fieldset>
		<legend>Catégories à affiche sur la fiche</legend>
		<dl>
			{foreach from=$categories item="cat"}
				{input type="checkbox" name="selected[]" value=$cat label=$cat default=$cat}
			{/foreach}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="print" label="Télécharger la fiche" shape="pdf" class="main"}
	</p>
</form>

{include file="_foot.tpl"}