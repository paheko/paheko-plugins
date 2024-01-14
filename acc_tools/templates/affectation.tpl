{include file="_head.tpl" title="Affectation de comptes à un export"}

<nav class="tabs">
{if $csv->ready() || !$csv->loaded()}
	<aside>
		{linkbutton shape="settings" label="Configurer les règles" href="affectation_config.php" target="_dialog"}
	</aside>
{/if}
{if !$csv->loaded()}
	<p>{linkbutton href="./" label="Retour" shape="left"}</p>
{/if}
</nav>

{form_errors}

{if $csv->ready()}
	<form method="post" action="{$self_url}">
		{csrf_field key=$csrf_key}
		{button type="submit" name="cancel" value="1" label="Annuler" shape="left"}
	</form>

	<p class="help">
		S'affichent ici les lignes du tableau importé, avec les comptes selon les règles d'affectation configurées.<br />
		Une fois que vous êtes satisfait du résultat, exportez le fichier en CSV pour pouvoir l'importer en tant qu'import simple dans la comptabilité.
	</p>

	<form method="get" action="">
		<p class="actions-spaced">
			{input type="select" name="show" options=$show_options default=$show required=true onchange="this.form.submit();"}
			{exportmenu table=true}
		</p>
	</form>
	<table class="list">
		<thead>
			<tr>
				<td>Date</td>
				<td>Libellé</td>
				<td>Montant</td>
				<td>Compte de débit</td>
				<td>Compte de crédit</td>
			{foreach from=$header item="label"}
				<td>{$label}</td>
			{/foreach}
			</tr>
		</thead>
		<tbody>
			{foreach from=$lines item="row"}
			<?php $empty = empty($row->debit_account) && empty($row->credit_account); ?>
			{if ($show === 'empty' && !$empty) || ($show == 'not_empty' && $empty)}
				{continue}
			{/if}
			<tr{if $empty} class="disabled"{/if}>
				<td>{$row.date|date_short}</td>
				<td>{$row.label}</td>
				<td class="money">{$row.amount|money_html|raw}</td>
				<td>{$row.debit_account}</td>
				<td>{$row.credit_account}</td>
				{foreach from=$header key="key" item="unused"}
					<td>{$row->$key|escape|nl2br}</td>
				{/foreach}
			</tr>
			{/foreach}
		</tbody>
	</table>

{elseif $csv->loaded()}
	<form method="post" action="{$self_url}">
		{include file="common/_csv_match_columns.tpl"}

		<p class="submit">
			{csrf_field key=$csrf_key}
			{button type="submit" name="cancel" value="1" label="Annuler" shape="left"}
			{button type="submit" name="preview" label="Prévisualiser" class="main" shape="right"}
		</p>
	</form>
{else}
	<div class="block help">
		<p>Cet outil permet de renseigner automatiquement les colonnes "Compte de débit" et "Compte de crédit" à un export CSV Paheko au format simple.</p>
		<p>C'est utile pour importer d'un coup de nombreuses opérations répétitives depuis un export bancaire ou PayPal par exemple.</p>
	</div>

	<form method="post" action="{$self_url}" enctype="multipart/form-data">

		<fieldset>
			<legend>Importer un fichier</legend>
			<dl>
				{input type="file" name="file" label="Fichier à importer" accept="csv" required=true}
				{include file="common/_csv_help.tpl" csv=$csv}
			</dl>

		</fieldset>

		<p class="submit">
			{csrf_field key=$csrf_key}
			{button type="submit" name="load" label="Charger le fichier" shape="right" class="main"}
		</p>

	</form>
{/if}

{include file="_foot.tpl"}
