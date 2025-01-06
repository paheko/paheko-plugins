{include file="_head.tpl" title=$title}

{include file="./_nav.tpl" current="all"}

<nav class="tabs">
	<aside>
		{if !$filters.start}
			{linkbutton shape="search" href="#" id="filterFormButton" label="Filtrer par dates" onclick="var a = $('#filterForm'); a.disabled = false; g.toggle(a, true); this.remove(); var a = $('#compareFormButton'); a ? a.remove() : null; return false;"}
		{/if}
		{exportmenu right=true}
	</aside>

	{if $logged_user.id && (!$filters.id_user || $filters.self) && !$filters.id_task}
	<ul class="sub">
		<li {if !$filters}class="current"{/if}>{link href=$self_url_no_qs label="Tous les membres"}</li>
		{if $logged_user.id}
		<li {if $filters.except}class="current"{/if}>{link href="?except" label="Sauf moi-même"}</li>
		<li {if $filters.self}class="current"{/if}>{link href="?self" label="Uniquement moi-même"}</li>
		{/if}
	</ul>
	{elseif isset($subtitle)}
	<ul class="sub">
		<li class="title">{$subtitle}</li>
	</ul>
	{/if}
</nav>

{include file="./_filters.tpl"}

{$list->getHTMLPagination()|raw}

	{include file="common/dynamic_list_head.tpl"}

		{foreach from=$list->iterate() item="task"}
		<tr>
			<td>{if $is_admin}{link href="!users/details.php?id=%d"|args:$task.user_id label=$task.user_name}{else}{$task.user_name}{/if}</td>
			<td>
				{if $filters.id_task}
					{$task.notes}
				{else}
					{if $task.task}
						<h4>{$task.task}</h4>
					{else}
						— Non spécifiée —
					{/if}
					{if $task.notes}<small>{$task.notes|escape|nl2br}</small>{/if}
				{/if}
			</td>
			<td>{$task.year}</td>
			<td>{$task.week}</td>
			<td>{$task.date|taima_date:'d MMMM yyyy'}</td>
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