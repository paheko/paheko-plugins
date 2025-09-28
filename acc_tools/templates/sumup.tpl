{include file="_head.tpl" title="Conversion SumUp"}

{form_errors}

<p>{linkbutton href="./" label="Retour" shape="left"}</p>


<form method="post" action="" enctype="multipart/form-data" data-disable-progress="1">
	<fieldset>
		<legend>Charger un fichier CSV SumUp</legend>
		<dl>
			{input type="file" name="csv" required=true label="Fichier CSV" accept="csv"}
			{input type="radio" name="group_fees" value=1 default=1 label="Regrouper toutes les commissions en une seule écriture"}
			{input type="radio" name="group_fees" value=0 label="Créer une écriture pour chaque commission prélevée"}
			{input type="checkbox" name="only_paid" value=1 default=1 label="Exporter uniquement les lignes marquées comme payées"}
		</dl>
		<p class="submit">
			{csrf_field key=$csrf_key}
			{button type="submit" label="Charger" shape="right" class="main" name="load"}
		</p>
	</fieldset>
</form>

{include file="_foot.tpl"}
