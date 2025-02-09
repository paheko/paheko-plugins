{include file="_head.tpl" current="plugin_pim" title="Import VCard"}

{form_errors}

<form method="post" action="" data-focus="#f_title" enctype="multipart/form-data">
<fieldset>
	<legend>Importer un fichier .vcf</legend>
	<dl>
		{input type="file" accept=".vcf,.VCF,.vcard,text/vcard" name="file" required=true label="Fichier .vcf"}
		{input type="checkbox" name="archived" value=1 label="Archiver ces contacts"}
	</dl>
</fieldset>

<p class="submit">
	{csrf_field key=$csrf_key}
	{button type="submit" name="upload" label="Envoyer" class="main" shape="right"}
</p>
</form>

{include file="_foot.tpl"}
