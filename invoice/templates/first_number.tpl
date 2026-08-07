{include file="_head.tpl" title="Premier numéro" current="plugin_invoice"}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1">

<fieldset>
	{if $invoice->isQuote()}
		<legend>Premier devis</legend>
		<p class="alert block">Merci d'indiquer le numéro de ce premier devis. Les autres numéros suivront et ne pourront pas être modifiés.</p>
		<p class="help">Si vous aviez déjà édité d'autres devis cette année, prenez le numéro qui suit le dernier devis émis.</p>
		<dl>
			{input required=true name="number" type="number" step=1 label="Numéro du devis" default="1" help="Le numéro sera préfixé avec l'année."}
		</dl>
	{elseif $invoice->type === $invoice::TYPE_CREDIT}
		<legend>Premier avoir</legend>
		<p class="alert block">Merci d'indiquer le numéro de ce premier avoir. Les autres numéros suivront et ne pourront pas être modifiés.</p>
		<p class="help">Si vous aviez déjà édité d'autres avoirs cette année, prenez le numéro qui suit le dernier avoir émis.</p>
		<dl>
			{input required=true name="number" type="number" step=1 label="Numéro de l'avoir" default="1" help="Le numéro sera préfixé avec l'année."}
		</dl>
	{else}
		<legend>Première facture</legend>
		<p class="alert block">Merci d'indiquer le numéro de cette première facture. Les autres numéros suivront et ne pourront pas être modifiés.</p>
		<p class="help">Si vous aviez déjà édité d'autres factures cette année, prenez le numéro qui suit la dernière facture émise.</p>
		<dl>
			{input required=true name="number" type="number" step=1 label="Numéro de la facture" default="1" help="Le numéro sera préfixé avec l'année."}
		</dl>
	{/if}
</fieldset>

<p class="submit">
	{button type="submit" name="validate" label="Continuer" shape="right" class="main"}
	{csrf_field key=$csrf_key}
</p>

</form>

{include file="_foot.tpl"}
