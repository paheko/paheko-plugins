{include file="_head.tpl" title="Importer une facture" current="plugin_invoice"}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1" enctype="multipart/form-data">

<fieldset>
	<legend>Informations</legend>
	<dl>
		{input type="file" required=true name="invoice" label="Fichier de facture à importer" accept=$accepted_files}
	</dl>
</fieldset>

<p class="submit">
	{button type="submit" name="save" label="Importer" shape="right" class="main"}
	{csrf_field key=$csrf_key}
</p>

</form>

{include file="_foot.tpl"}
