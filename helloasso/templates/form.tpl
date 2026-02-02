{include file="_head.tpl" title="%s — Configurer"|args:$f.name}

{include file="./_menu.tpl" current="home" current_sub="config"}

{if $_GET.msg === 'SAVED'}
<p class="confirm block">
	Configuration de la campagne enregistrée.
</p>
{/if}

{form_errors}

{if count($tiers)}
<h2 class="ruler">Tarifs</h2>
<table class="list">
	<thead>
		<tr>
			<td scope="col">Type</th>
			<th scope="col">Nom du tarif</th>
			<td scope="col" class="money">Montant</td>
			<td scope="col" class="num">Compte</td>
			<td scope="col" class="num">Création membre</td>
			<td scope="col" class="num">Inscription activité</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$tiers item="tier"}
		<?php $account = $tier->getAccountCode(); ?>
		<tr>
			<td>{tag label=$tier->getTypeLabel() color=$tier->getTypeColor()}</td>
			<th scope="row">{if $tier.label}{$tier.label}{else}—{/if}</th>
			<td class="money">{if $tier->getTypeAccount() === 'donation' && !$tier.amount}(libre){else}{$tier.amount|money_currency|raw}{/if}</td>
			<td class="num">{if $account}{tag color="darkgreen" label=$account}{/if}</td>
			<td class="num">
				{if $tier.create_user === $tier::NO_USER_ACTION}
					{tag label="Pas de lien avec les membres"}
				{elseif $tier.create_user === $tier::CREATE_UPDATE_USER}
					{tag label="Oui" color="darkorange"}
				{else}
					{tag label="Non" color="darkgreen"}
				{/if}
			</td>
			<td class="num">{if $tier.id_fee}{tag color="darkgreen" label="Oui"}{else}{tag label="Non"}{/if}</td>
			<td class="actions">
				{linkbutton shape="edit" label="Configurer" href="form_tier.php?id=%d"|args:$tier.id}
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>
{/if}

{if count($options)}
<h2 class="ruler">Options</h2>
<table class="list">
	<thead>
		<tr>
			<th scope="col">Nom</th>
			<td scope="col" class="money">Montant</td>
			<td scope="col" class="num">Compte</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$options item="option"}
		<?php $account = $option->getAccountCode(); ?>
		<tr>
			<th scope="row">{if $option.label}{$option.label}{else}<em>(pas de nom défini)</em>{/if}</th>
			<td class="money">{$option.amount|money_currency|raw}</td>
			<td class="num">{if $account}{tag color="darkgreen" label=$account}{/if}</td>
			<td class="actions">
				{linkbutton shape="edit" label="Configurer" href="form_option.php?id=%d"|args:$option.id}
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>
{/if}


<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Synchronisation avec la comptabilité</legend>
		<dl>
			{input type="select" options=$years_assoc name="id_year" source=$f required=false label="Exercice comptable" default_empty="— Ne pas synchroniser —"}
			<dd class="help">Si un exercice est sélectionné, les commandes passées avec cette campagne et ayant été payées seront transformées en écritures comptables selon la configuration des tarifs et options.</dd>
			{input type="list" target="!acc/charts/accounts/selector.php?types=6&key=code" name="payment_account_code" label="Compte de recette pour les paiements reçus" default=$payment_account}
		</dl>
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
	</p>
</form>

{include file="_foot.tpl"}
