{include file="_head.tpl" title="Événements de stock"}

{include file="../_nav.tpl" current='stock' subcurrent="events"}

{if $list->count()}
	{include file="common/dynamic_list_head.tpl"}
	{foreach from=$list->iterate() item="event"}
		<tr>
			<td>{$event.date|date}</td>
			<td>{$event.type_label}</td>
			<th>{$event.label}</th>
			<td class="actions">
				{linkbutton href="details.php?id=%d"|args:$event.id label="Détails" shape="menu"}
				{linkbutton href="edit.php?id=%d&delete"|args:$event.id label="Supprimer" shape="delete" target="_dialog"}
			</td>
		</tr>
	{/foreach}
		</tbody>
	</table>

	{$list->getHTMLPagination()|raw}
{else}
	<p class="alert block">Aucun événement</p>
{/if}

{include file="_foot.tpl"}