{include file="admin/_head.tpl" title="Git Documents — Configuration" current="plugin_%s"|args:$plugin.id}

{form_errors}

{if isset($_GET['ok'])}
	<p class="confirm block">La configuration a été enregistrée.</p>
{/if}

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Configuration</legend>
		<dl>
			{input type="email" name="diff_email" required=false default=$plugin.config.diff_email label="Adresse e-mail pour l'envoi des diff" help="Si laissé vide, aucun email ne sera envoyé, sinon un diff sera envoyé à chaque modification"}
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button name="save" label="Enregistrer" shape="right" type="submit" class="main"}
	</p>
</form>

{include file="admin/_foot.tpl"}