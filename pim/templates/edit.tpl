{include file="_head.tpl" current="plugin_pim" hide_title=true plugin_css=['calendar.css']}

{form_errors}

<form method="post" action="" data-focus="2">
<fieldset>
	<legend>{$title}</legend>
	<dl>
		{input type="select" name="id_category" label="Catégorie" options=$categories_assoc required=true}
		{input type="text" name="title" label="Titre" source=$event required=true}
		{input type="textarea" name="desc" label="Description" source=$event cols=50 rows=6}
		{input type="text" name="location" label="Adresse" source=$event}
		<dd>{linkbutton href="#" label="Ouvrir l'adresse sur une carte"}</dd>
		{input type="number" min=0 required=true name="reminder" label="Rappel" suffix=" minutes avant" source=$event}
	</dl>
</fieldset>
<fieldset>
	<legend>Dates</legend>
	<dl>
		{input type="datetime" name="date" source=$event required=true label="Début" default=$start}
		{input type="datetime" name="date_end" source=$event required=false label="Fin" default=$end}
		{input type="checkbox" name="all_day" value=1 source=$event label="Toute la journée"}
		{input type="select_groups" name="timezone" required=true source=$event label="Fuseau horaire" options=$timezones default=$default_tz}
	</dl>
</fieldset>
<p class="actions">
	{linkbutton shape="plus" href="edit.php?copy=%d"|args:$event.id label="Dupliquer"}
	{linkbutton shape="delete" href="delete.php?id=%d"|args:$event.id label="Supprimer"}
</p>

<p class="submit">
	{csrf_field key=$csrf_key}
	{button type="submit" name="save" label="Enregistrer" class="main" shape="right"}
</p>
</form>

{*
<script type="text/javascript">
var categories = '.json_encode($cats).';
var hsl_sl = "'.Agenda::ALL_DAY.'";

window.addEventListener("load", function () {
	var cat_select = document.getElementById("f_category");
	var cat_id = cat_select.value;
	var pr = document.createElement("span");
	pr.id = "cat_preview";
	pr.style.backgroundColor = "hsl(" + categories[cat_id].color + ", " + hsl_sl + ")";

	cat_select.parentNode.insertBefore(pr, cat_select);

	cat_select.addEventListener("change", function() {
		document.getElementById("cat_preview").style.backgroundColor = "hsl(" + categories[this.value].color + ", " + hsl_sl + ")";
	}, false);
}, false);
</script>
*}

{include file="_foot.tpl"}
