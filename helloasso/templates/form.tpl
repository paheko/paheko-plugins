{include file="_head.tpl" title="%s — Configurer le formulaire"|args:$haform.name}

{include file="./_menu.tpl" current_sub=null}

{if $_GET.ok !== null}
<p class="confirm block">
	Configuration enregistrée.
</p>
{/if}

{form_errors}

<table class="list">
	<thead>
		<tr>
			<td scope="col">Type</th>
			<th scope="col">Nom du tarif</th>
			<td scope="col" class="money">Montant</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$tiers item="tier"}
		<tr>
			<td>{tag label=$tier->getTypeLabel() color=$tier->getTypeColor()}</td>
			<th scope="row">{if $tier.label}{$tier.label}{else}<em>(pas de nom défini)</em>{/if}</th>
			<td class="money">{$tier.amount|money_currency|raw}</td>
			<td class="actions">
				{linkbutton shape="edit" label="Modifier" href="form_tier.php?id=%d"|args:$tier.id}
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Synchronisation avec la comptabilité</legend>
		<dl>
			{input type="select" options=$years_assoc name="id_year" source=$haform required=true label="Exercice comptable" default_empty="— Ne pas synchroniser —"}
			<dd class="help">Si un exercice est sélectionné, les commandes passées avec ce formulaire et ayant été payées seront transformées en écritures comptables selon la configuration des tarifs et options.</dd>
		</dl>
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
	</p>
</form>

{include file="_foot.tpl"}
