{include file="_head.tpl" current="plugin_pim" title="Import iCalendar"}

{form_errors}

<form method="post" action="" data-focus="#f_title" enctype="multipart/form-data">
<fieldset>
	<legend>Importer un fichier .ics</legend>
	<dl>
		{input type="file" accept=".ics,.ICS,text/calendar" name="file" required=true label="Fichier .ics"}
	</dl>
</fieldset>


<p class="submit">
	{csrf_field key=$csrf_key}
	{button type="submit" name="upload" label="Envoyer" class="main" shape="right"}
</p>
</form>

{include file="_foot.tpl"}
