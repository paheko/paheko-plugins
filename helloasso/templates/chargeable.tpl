{include file="_head.tpl" title="Article HelloAsso n°%s"|args:$chargeable.id}

{include file="./_menu.tpl" current="home"}

{if array_key_exists('ok', $_GET)}
	<p class="confirm block">Article mis à jour avec succès.</p>
{/if}

<h2 class="ruler">Informations sur l'article</h2>

<dl class="describe">
	<dt>Label</dt>
	<dd>{$chargeable->label}</dd>
	<dt>Type</dt>
	<dd>
		{if $chargeable->id_item !== null}
			{assign var='types' value=Plugin\HelloAsso\Entities\Item::TYPES}
			{$types[$parent_item->type]}
			{if $chargeable->type === Plugin\HelloAsso\Entities\Chargeable::OPTION_TYPE}- Option{/if}
		{else}
			{assign var='types' value=Plugin\HelloAsso\Entities\Form::TYPES}
			{$types[$form->type]}
		{/if}
	</dd>
	<dt>Montant</dt>
	<dd>{if null === $chargeable->amount}S'applique peu importe le montant.{elseif $chargeable->type === Plugin\HelloAsso\Entities\Chargeable::FREE_TYPE}Gratuit{else}{$chargeable->amount|money_currency|raw}{/if}</dd>
	<dt>Formulaire</dt>
	<dd>{$form->name}</dd>
	<dt>Statut</dt>
	<dd>
		{if ($plugin->config->accounting && $chargeable->type !== Plugin\HelloAsso\Entities\Chargeable::FREE_TYPE && (!$chargeable->id_credit_account || !$chargeable->id_debit_account)) || ($chargeable->register_user === null)}
			<em>En attente de configuration {if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)}de votre part{else}par un·e administrateur/trice.{/if}</em>
			{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)}<br />Veuillez configurer les options ci-dessous.{/if}
		{else}
			En fonctionnement.
		{/if}
	</dd>
</dl>

{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)}
	<form method="post" action="{$self_url}">
		<fieldset>
			<legend>Inscription</legend>
			{if !$category}
				<p class="alert block">Vous devez <a href="{$plugin_admin_url}config.php">configurer une catégorie</a> valide pour pouvoir sélectionner l'option d'inscription de membres.</p>
			{else}
				<dl>
					{input type="radio" name="register_user" label="Inscrire comme membre \"%s\""|args:$category.name source=$chargeable value="1" required=true help="Inscrira automatiquement la personne comme membre Paheko si cet article est commandé."}
					{input type="radio" name="register_user" label="Ne pas inscrire" source=$chargeable value="0" required=true}
				</dl>
				<p class="help block">
					La catégorie d'inscription peut être modifiée {if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)}dans <a href="{$plugin_admin_url}config.php">la configuration de l'extension</a>{else}par un·e adminstrateur/trice{/if}.
				</p>
			{/if}
		</fieldset>
		{if $plugin->config->accounting && $chargeable->type !== Plugin\HelloAsso\Entities\Chargeable::FREE_TYPE}
			<fieldset>
				<legend>Comptabilité</legend>
				<dl>
					{input type="list" target="!acc/charts/accounts/selector.php?targets=%s&chart=%d"|args:'6':$chart_id name="credit" label="Type de recette" required=true default=$credit_account}
					{input type="list" target="!acc/charts/accounts/selector.php?targets=%s&chart=%d"|args:'1:2:3':$chart_id name="debit" label="Compte d'encaissement" required=true default=$debit_account}
				</dl>
				<p class="help block">Cette modification impacte uniquement les <em>futures</em> synchronisations. Elle n'est pas rétro-active.</p>
			</fieldset>
		{/if}
		<p class="submit">
			{csrf_field key=$csrf_key}
			{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
		</p>
	</form>
{/if}

{if $TECH_DETAILS}
	<dl style="background-color: black; color: limegreen; padding-top: 0.8em;" class="describe">
		<dt style="color: limegreen;">item</dt>
		<dd><pre>{if $chargeable->id_item}{$parent_item|var_dump}{else}NULL{/if}</pre></dd>
		<dt style="color: limegreen;">item->raw_data</dt>
		<dd><pre>{if $chargeable->id_item}{$parent_item->raw_data|json_revamp}{else}NULL{/if}</pre></dd>
	</dl>
{/if}

{include file="_foot.tpl"}