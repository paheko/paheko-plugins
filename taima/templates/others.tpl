{include file="_head.tpl" title="Autres membres" plugin_css=['style.css'] current="plugin_taima"}

{include file="%s/templates/_nav.tpl"|args:$plugin_root current="others"}

{if $user}
	<h2 class="ruler">{$user.identite}</h2>
{/if}

{$list->getHTMLPagination()|raw}

{include file="common/dynamic_list_head.tpl"}

		{foreach from=$list->iterate() item="task"}
		<tr>
			<th>
				{if $task.task}
					{$task.task}
				{else}
					—Indéfini—
				{/if}
				{if $task.notes}<br /><small>{$task.notes|escape|nl2br}</small>{/if}
			</th>
			<td>{$task.year}</td>
			<td>{$task.week}</td>
			<td>{$task.date|taima_date:'d MMMM YYYY'}</td>
			<td>{$task.duration|taima_minutes}</td>
			<td>{$task.user_name}</td>
			<td>
				{linkbutton href="others_delete.php?id=%d"|args:$task.id label="Supprimer" shape="delete" target="_dialog"}
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>

{$list->getHTMLPagination()|raw}

{include file="_foot.tpl"}