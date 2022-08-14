{include file="_head.tpl" title="Historique produit : %s"|args:$product.name current="plugin_%s"|args:$plugin.id}

{include file="%s/manage/_nav.tpl"|args:$pos_templates_root current='products'}

<p>
{if $events_only}
	{linkbutton href="?id=%d"|args:$product.id label="Afficher tous les événements" shape="eye"}
{else}
	{linkbutton href="?id=%d&events_only"|args:$product.id label="Cacher les ventes" shape="eye-off"}
{/if}
</p>

<table class="list">
	<thead>
		<tr>
			<th>Date</th>
			<td>Type</td>
			<td>Événement</td>
			<td class="num">Modification du stock</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$history item="row"}
			<tr>
				<th>{$row.date|date}</th>
				<td>
					{if $row.item}
						Vente
					{elseif $row.event_type == 0}
						Événement
					{elseif $row.event_type == 1}
						Inventaire
					{elseif $row.event_type == 2}
						Réception commande
					{/if}
				</td>
				<td>{$row.event_label}</td>
				<td class="num">
					{if $row.event_type == 1}={elseif $row.change > 0}+{/if}{$row.change}
				</td>
				<td class="actions">
					{if $row.tab}
						{linkbutton href="%stab.php?id=%d"|args:$plugin_url,$row.tab label="Note de caisse" shape="menu"}
					{elseif $row.event}
						{linkbutton href="%smanage/stock/details.php?id=%d"|args:$plugin_url,$row.event label="Événement de stock" shape="table"}
					{/if}
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>

{include file="_foot.tpl"}