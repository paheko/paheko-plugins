{include file="_head.tpl" title="Ouverture de caisse"}

{if $current_pos_session}
<p class="alert block">
	Attention : il existe déjà une caisse ouverte en cours, voulez-vous vraiment ouvrir deux sessions de caisse en même temps&nbsp;?<br />
</p>
{/if}

{form_errors}

<form method="post" action="" data-focus="1">
	{if $plugin.config.allow_custom_user_name || count($locations)}
	<fieldset>
		<legend>Informations d'ouverture</legend>
		<dl>
			{if $plugin.config.allow_custom_user_name}
				{input type="text" name="user_name" label="Nom de la personne procédant à l'ouverture de la caisse" required=true default=$user_name}
			{/if}
			{if count($locations)}
				{input type="select" name="id_location" label="Lieu de vente" default_empty="— Choisir un lieu —" options=$locations required=true}
			{/if}
		</dl>
	</fieldset>
	{/if}

	<div class="pos-balances pos-balances-count-{$balances|count}">
	{foreach from=$balances item="balance"}
		<fieldset>
			<legend>{$balance.name}</legend>
			<dl>
				{input type="money" name="balances[%d]"|args:$balance.id label="Solde à l'ouverture" required=true}
				{if count($balances) == 1}
				<dd class="help">Ne compter que les espèces, pas les chèques.</dd>
				{/if}
			</dl>
		</fieldset>
	{/foreach}
	</div>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{linkbutton shape="left" href="./" label="Annuler"}
		{button type="submit" shape="right" label="Ouvrir la caisse" class="main" name="open"}
	</p>
</form>

{include file="_foot.tpl"}