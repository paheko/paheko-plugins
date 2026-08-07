{include file="_head.tpl" title=$title current="plugin_invoice"}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1">

<fieldset>
	<legend>Informations</legend>
	<dl>
		{input required=true name="label" type="text" label="Objet" source=$line}
		{input required=false name="reference" type="text" label="Référence" source=$line}
		{input required=false name="description" type="textarea" cols=50 rows=3 label="Description" source=$line}
	</dl>
</fieldset>

<fieldset>
	<legend>Montant</legend>
	<dl>
		{input type="money" name="price" required=true label="Prix unitaire" source=$line}
		{input type="number" name="quantity" required=true label="Quantité" source=$line default=1 step="0.001"}
		<dd>{input type="select" name="unit" required=true source=$line options=$line::UNITS}</dd>
		{input type="select" name="vat_rate" required=true label="Taux de TVA" source=$line options=$line->getVATRatesOptions()}
	</dl>
</fieldset>

<p class="submit">
	{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
	{csrf_field key=$csrf_key}
</p>

</form>

{include file="_foot.tpl"}
