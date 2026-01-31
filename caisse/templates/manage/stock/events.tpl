{include file="_head.tpl" title="Événements de stock"}

{include file="../_nav.tpl" current='stock' subcurrent="events"}

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

{include file="_foot.tpl"}