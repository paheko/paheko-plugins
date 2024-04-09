{include file="_head.tpl" title="Configuration"}

{include file="./_nav.tpl" current="config"}

<form method="post" action="">

<fieldset>
	<legend>Options possibles</legend>
	<p class="help">
		Indiquer une valeur par ligne.
	</p>
	<dl>
		{input type="textarea" name="sources" label="Provenances" default=$defaults.sources required=true cols=30 rows=10}
		{input type="textarea" name="sources_details" label="Détail de la provenance des vélos" default=$defaults.sources_details required=true cols=30 rows=10 help="Il sera toujours possible de saisir un texte libre, ces options seront juste proposées par défaut."}
		{input type="textarea" name="raisons_sortie" label="Raisons de sortie de vélo" default=$defaults.raisons_sortie required=true cols=30 rows=10}
		{input type="textarea" name="types" label="Types de vélos" default=$defaults.types required=true cols=30 rows=10}
		{input type="textarea" name="tailles" label="Tailles des roues" default=$defaults.tailles required=true cols=30 rows=10}
		{input type="textarea" name="genres" label="Genres des vélos" default=$defaults.genres required=true cols=30 rows=10}
	</dl>
</fieldset>


<p class="submit">
	{csrf_field key=$csrf_key}
	{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
</p>

</form>

{include file="_foot.tpl"}