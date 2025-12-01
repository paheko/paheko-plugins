{include file="_head.tpl" title=$title}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1">
	<fieldset>
		<legend>Créditer le porte-monnaie du membre</legend>
		<dl>
			{input type="money" name="amount" label="Montant à créditer" required=true}
			{if count($list) > 1}
				{input type="select" name="id_method" options=$list required=true label="Moyen de paiement" default=$id_default_method}
			{/if}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="save" label="Créditer" shape="right" class="main"}
	</p>
</form>

{include file="_foot.tpl"}