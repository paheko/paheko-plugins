{include file="_head.tpl" title="Options du champ"}

{form_errors}

<form method="post" action="">

<fieldset>
	<legend>Options possibles</legend>
	<p class="help">
		Indiquer une valeur par ligne.
	</p>
	<dl>
		{input type="textarea" name=$field.name label=$field.label default=$options required=true cols=30 rows=10}
		{if $field.name === 'source_details'}
			<p class="help">Il sera toujours possible de saisir un texte libre, ces options seront juste proposées par défaut.</p>
		{/if}
	</dl>
</fieldset>


<p class="submit">
	{csrf_field key=$csrf_key}
	{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
</p>

</form>

{include file="_foot.tpl"}