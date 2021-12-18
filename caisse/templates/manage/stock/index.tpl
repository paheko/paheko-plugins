{include file="admin/_head.tpl" title="Gestion stock" current="plugin_%s"|args:$plugin.id}

{include file="%s/manage/_nav.tpl"|args:$pos_templates_root current='stock'}

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

{include file="admin/_foot.tpl"}