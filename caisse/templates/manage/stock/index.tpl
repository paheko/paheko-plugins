{include file="_head.tpl" title="Gestion stock"}

{include file="./_nav.tpl" current='stock'}

<h2 class="ruler">Événements de stock</h2>

{if count($list)}
	<table class="list">
		<thead>
			<tr>
				<td>Date</td>
				<td>Type</td>
				<th>Événement</th>
				<td></td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$list item="event"}
				<tr>
					<td>{$event.date|date}</td>
					<td>{$event::TYPES[$event.type]}</td>
					<th>{$event.label}</th>
					<td class="actions">
						{linkbutton href="details.php?id=%d"|args:$event.id label="Détails" shape="menu"}
						{linkbutton href="edit.php?id=%d&delete"|args:$event.id label="Supprimer" shape="delete" target="_dialog"}
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{else}
	<p class="alert block">Aucun événement</p>
{/if}

<h2 class="ruler">Stock actuel</h2>

<table class="list">
	<thead>
		<tr>
			<th>Catégorie</th>
			<td>Produits en stock</td>
			<td class="money">Valeur des produits</td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$value_list item="row"}
			<tr>
				<th>{$row.label}</th>
				<td>{$row.count}</td>
				<td class="money">{$row.value|raw|money_currency}</td>
			</tr>
		{/foreach}
	</tbody>
</table>

{include file="_foot.tpl"}