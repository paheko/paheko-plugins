{include file="_head.tpl" current="plugin_pim" hide_title=true plugin_css=['calendar.css']}

{form_errors}

<form method="post" action="" data-focus="#f_title">
<aside class="secondary">
	{if $event->exists()}
	<p class="actions">
		{linkbutton shape="plus" href="edit.php?copy=%d"|args:$event.id label="Dupliquer"}
		{linkbutton shape="delete" href="delete.php?id=%d"|args:$event.id label="Supprimer"}
	</p>
	{/if}

	<fieldset>
		<dl>
			{input type="textarea" name="desc" label="Description" source=$event cols=30 rows=5}
			{input type="text" name="location" label="Adresse" source=$event}
			<dd>{linkbutton href="#" label="Ouvrir l'adresse sur une carte"}{*FIXME*}</dd>
			{input type="number" min=0 required=true name="reminder" label="Rappel" suffix=" minutes avant" source=$event}
		</dl>
	</fieldset>
</aside>
<fieldset>
	<legend>{$title}</legend>
	<dl>
		{input type="select" name="id_category" label="Catégorie" options=$categories_assoc required=true source=$event}
		{input type="text" name="title" label="Titre" source=$event required=true}
	</dl>
</fieldset>

<fieldset>
	<legend>Dates</legend>
	<dl>
		{input type="checkbox" name="all_day" value=1 source=$event label="Toute la journée"}
		{input type="datetime" name="start" source=$event required=true label="Début"}
		{input type="datetime" name="end" source=$event required=false label="Fin"}
		{input type="select_groups" name="timezone" required=true source=$event label="Fuseau horaire" options=$timezones}
	</dl>
</fieldset>


<p class="submit">
	{csrf_field key=$csrf_key}
	{button type="submit" name="save" label="Enregistrer" class="main" shape="right"}
</p>
</form>

<script type="text/javascript">
var categories = {$categories_export|escape:'json'};
</script>

<script type="text/javascript" src="event_edit.js"></script>

{include file="_foot.tpl"}
