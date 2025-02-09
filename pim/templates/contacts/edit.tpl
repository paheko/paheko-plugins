{include file="_head.tpl" current="plugin_pim" hide_title=true plugin_css=['calendar.css']}

{form_errors}

<form method="post" action="" data-focus="#f_first_name" enctype="multipart/form-data">
<aside class="secondary">
	<fieldset>
		<dl>
		{input type="checkbox" name="archived" label="Archivé" source=$contact value=1}
		{input type="textarea" name="notes" label="Notes" source=$contact required=false}
		{input type="date" name="birthday" label="Date de naissance" source=$contact required=false}
		{input type="file" name="photo" label="Photo" help="Laisser vide pour ne pas changer la photo." accept="image"}
		</dl>
	</fieldset>
</aside>

<fieldset>
	<legend>{$title}</legend>
	<dl>
		{input type="text" name="first_name" label="Prénom" source=$contact required=true}
		{input type="text" name="last_name" label="Nom" source=$contact required=false}
		{input type="text" name="title" label="Contexte" source=$contact required=false}
		{input type="tel" name="mobile_phone" label="Numéro de mobile" source=$contact required=false}
		{input type="tel" name="phone" label="Numéro de fixe" source=$contact required=false}
		{input type="textarea" rows=3 name="address" label="Adresse postale" source=$contact required=false}
		{input type="email" name="email" label="Adresse e-mail" source=$contact required=false}
		{input type="url" name="web" label="Adresse du site web" source=$contact required=false}
	</dl>
</fieldset>

<p class="submit">
	{csrf_field key=$csrf_key}
	{button type="submit" name="save" label="Enregistrer" class="main" shape="right"}
</p>
</form>

{include file="_foot.tpl"}
