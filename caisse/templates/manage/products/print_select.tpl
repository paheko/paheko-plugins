{include file="_head.tpl" title="Impression fiche produits"}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1" data-disable-progress="1">
	<fieldset>
		<legend>Catégories à affiche sur la fiche</legend>
		<dl>
			{input type="checkbox" value=1 default=1 label="Tout cocher / décocher" name="" onchange="checked = this.checked; document.querySelectorAll('input[type="checkbox"]').forEach(c => c.checked = checked);"}
			<dt>Catégories</dt>
			{foreach from=$categories key="id" item="cat"}
				{input type="checkbox" name="selected[]" value=$id label=$cat default=$id}
			{/foreach}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="print" label="Télécharger la fiche" shape="pdf" class="main"}
	</p>
</form>

{include file="_foot.tpl"}