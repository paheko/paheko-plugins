{include file="_head.tpl" title="Ouverture de caisse"}

{if $current_pos_session}
<p class="alert block">
	Attention : il existe déjà une caisse ouverte en cours, voulez-vous vraiment ouvrir deux sessions de caisse en même temps&nbsp;?<br />
</p>
{/if}

{form_errors}

{if !$id_location && count($locations)}
	<form method="post" action="" data-focus="1">
		<fieldset>
			<legend>Choisir un lieu de vente</legend>
			{foreach from=$locations item="name" key="id"}
			<p class="submit">
				{button type="submit" name="id_location" value=$id label=$name class="main"}
			</p>
			{/foreach}
		</fieldset>
	</form>
{else}
	<form method="post" action="" data-focus="1">
		{if $plugin.config.allow_custom_user_name}
		<fieldset>
			<legend>Informations d'ouverture</legend>
			<dl>
				{if $plugin.config.allow_custom_user_name}
					{input type="text" name="user_name" label="Nom de la personne procédant à l'ouverture de la caisse" required=true default=$user_name}
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
			<input type="hidden" name="id_location" value="{$id_location}" />
			{button type="submit" shape="right" label="Ouvrir la caisse" class="main" name="open"}
		</p>
	</form>
{/if}

{include file="_foot.tpl"}