{include file="admin/_head.tpl" title="Ouverture de caisse" current="plugin_%s"|args:$plugin.id}

{if $current_pos_session}
<p class="alert block">
	Attention : il existe déjà une caisse ouverte en cours, voulez-vous vraiment ouvrir deux sessions de caisse en même temps&nbsp;?
</p>
{/if}

{form_errors}

<form method="post" action="">
	<fieldset>
		<legend>Ouvrir la caisse</legend>
		<dl>
			{input type="money" name="amount" label="Solde de la caisse à l'ouverture" help="Uniquement les espèces, ne pas compter les chèques" required=true}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" shape="right" label="Ouvrir la caisse" class="main" name="open"}
	</p>
</form>

{include file="admin/_foot.tpl"}