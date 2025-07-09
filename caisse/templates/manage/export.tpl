{include file="_head.tpl" title="Export des données comptable"}

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Intervalle d'export</legend>
		<dl>
			{input type="date" label="Date de début" name="start" required=true default=$start}
			{input type="date" label="Date de fin" name="end" required=true default=$end}
			<dt>Format</dt>
			{input type="radio" name="format" value="ods" label="LibreOffice" default="ods"}
			{input type="radio" name="format" value="csv" label="CSV"}
			{input type="radio" name="format" value="xlsx" label="Excel"}
		</dl>
	</fieldset>
	<p class="submit">
		{button name="export" label="Créer un export CSV correspondant à ces dates" shape="right" type="submit" class="main"}
	</p>
</form>

{include file="_foot.tpl"}