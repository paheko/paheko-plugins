{include file="admin/_head.tpl" title="Historique produit : %s"|args:$product.name current="plugin_%s"|args:$plugin.id}

{include file="%s/manage/_nav.tpl"|args:$pos_templates_root current='products'}

<p>
{if $events_only}
	{linkbutton href="?id=%d"|args:$product.id label="Afficher tous les événements" shape="search"}
{else}
	{linkbutton href="?id=%d&events_only"|args:$product.id label="N'afficher que les événements liés aux modifications d'inventaire" shape="search"}
{/if}
</p>

<table class="list">
	<thead>
		<tr>
			<th>Date</th>
			<td>Événement</td>
			<td>Modification du stock</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$history item="row"}
			<tr>
				<th>{$row.date|date}</th>
				<td>{if $row.event_label}{$row.event_label}{else}Vente{/if}</td>
				<td>{if $row.change > 0}+{/if}{$row.change}</td>
				<td class="actions">
					{if $row.tab}
						{linkbutton href="%stab.php?id=%d"|args:$plugin_url,$row.tab label="Note" shape="menu"}
					{/if}
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>

{include file="admin/_foot.tpl"}