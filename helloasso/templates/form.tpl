{include file="admin/_head.tpl" title="%s — %s"|args:$form.org_name,$form.name current="plugin_%s"|args:$plugin.id}

{include file="%s/templates/_menu.tpl"|args:$plugin_root current="home"}

<table class="list">
	<thead>
		<tr>
			<td class="check"></td>
			<th>Réference</th>
			<td class="money">Montant</td>
			<td>Date</td>
			<td>Personne</td>
			<td>Statut</td>
			<td></td>
		</tr>
	<tbody>
		{foreach from=$list item="row"}
		<tr>
			<td class="check"></td>
			<th><a href="order.php?id={$row.id}">{$row.id}</a></th>
			<td class="money">{$row.amount.total|money_currency|raw}</td>
			<td>{$row.date|date}</td>
			<td>{$row.payer_name}</td>
			<td>{if $row.status}Payée{else}Paiement incomplet{/if}</td>
			<td class="actions">
				{linkbutton href="order.php?id=%s"|args:$row.id shape="help" label="Détails"}
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>

{if $restricted_results}
	<p class="alert block">
		<strong>{$restricted_results} autres résultats sont disponibles</strong> mais ne peuvent s'afficher que si vous avez un niveau de contribution de plus de 50 € à Garradin :)
	</p>
{else}
	{pagination url="?id=%d&p=[ID]"|args:$form.id page=$page bypage=$per_page total=$count}
{/if}


{include file="admin/_foot.tpl"}
