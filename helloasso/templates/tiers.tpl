{include file="_head.tpl" title="%s — Configurer les tarifs"|args:$f.name}

{if count($tiers)}
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
{/if}

{include file="_foot.tpl"}
