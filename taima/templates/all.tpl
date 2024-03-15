{include file="_head.tpl" title=$title}

{include file="./_nav.tpl" current="all"}

{$list->getHTMLPagination()|raw}

	{include file="common/dynamic_list_head.tpl"}

		{foreach from=$list->iterate() item="task"}
		<tr>
			<td>{link href="!users/details.php?id=%d"|args:$task.user_id label=$task.user_name}</td>
			<th>
				{if $filters.task_id}
					{$task.notes}
				{else}
					{if $task.task}
						{$task.task}
					{else}
						— Non spécifiée —
					{/if}
					{if $task.notes}<br /><small>{$task.notes|escape|nl2br}</small>{/if}
				{/if}
			</th>
			<td>{$task.year}</td>
			<td>{$task.week}</td>
			<td>{$task.date|taima_date:'d MMMM YYYY'}</td>
			<td>{$task.duration|taima_minutes}</td>
			<td class="actions">
				{linkbutton href="edit.php?id_user=%d&from=%d"|args:$task.user_id:$task.id label="Dupliquer" shape="plus" target="_dialog"}
				{linkbutton href="edit.php?id_user=%d&id=%d"|args:$task.user_id:$task.id label="Modifier" shape="edit" target="_dialog"}
				{linkbutton href="delete.php?id=%d"|args:$task.id label="Supprimer" shape="delete" target="_dialog"}
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>

{$list->getHTMLPagination()|raw}

{include file="_foot.tpl"}