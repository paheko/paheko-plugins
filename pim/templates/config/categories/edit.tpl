{include file="_head.tpl" current="plugin_pim" hide_title=true plugin_css=['calendar.css']}

{form_errors}

<form method="post" action="" class="edit-category">
<fieldset>
	<legend>{$title}</legend>
	<dl>
		{input type="text" name="title" label="Titre" required=true source=$cat}
		{input type="number" name="default_reminder" label="Rappel par défaut" required=true source=$cat default=15 suffix="minutes avant l'événement" size=3}
		{input type="hue" name="color" label="Couleur" required=true source=$cat data-saturation="50%" data-lightness="75%"}
	</dl>
</fieldset>
<p class="submit">
	{csrf_field key=$csrf_key}
	{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
</p>
</form>

{include file="_foot.tpl"}