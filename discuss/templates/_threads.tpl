{if !$threads_count}
	<p class="alert">{{There are no messages yet.}}</p>
{else}
	<table class="threads">
		<thead>
			<tr>
				<td class="icon"></td>
				<th>{{Subject}}</th>
				<td>{{Last message}}</td>
				<td>{{Replies}}</td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$threads item="thread"}
			<tr class="{if $thread->isClosed()}closed{/if} {if $thread->isPinned()}pinned{/if}">
				<td>
					{if $thread->isClosed()}
					C
					{/if}
					{if $thread->isPinned()}
					X
					{/if}
				</td>
				<th><a href="{$thread.uri}">{$thread.subject}</a></th>
				<td><a href="{$thread.uri}#last" title="{{Go to the last message}}">{$thread.last_update|relative_date}</a></td>
				<td>{{%n reply}{%n replies} n=$thread.replies_count}</td>
			</tr>
			{/foreach}
		</tbody>
	</table>

	<p class="pages">
		<?php
		$max = floor($threads_count / $per_page);
		for ($i = 1; $i < $max; $i++) {
			printf('<a href="?p=%d">%s</a> ', $i, $i == $page ? '<strong>' . $i .'</strong>' : $i);
		}
		?>
	</p>
{/if}