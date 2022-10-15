{include file="_head.tpl" title="Ouverture de caisse" current="plugin_%s"|args:$plugin.id}

<form method="post" action="" data-focus="1">
	<fieldset>
		<legend>Ouvrir la caisse</legend>
		<dl>
			{input type="money" name="amount" label="Solde de la caisse à l'ouverture" help="Uniquement les espèces, ne pas compter les chèques" required=true}
			{if $plugin.config.allow_custom_user_name}
				{input type="text" name="user_name" label="Nom de la personne procédant à l'ouverture de la caisse" required=true default=$user_name}
			{/if}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" shape="right" label="Ouvrir la caisse" class="main" name="open"}
	</p>
</form>

{include file="_foot.tpl"}