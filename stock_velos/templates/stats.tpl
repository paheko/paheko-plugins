{include file="_head.tpl" title="Statistiques"}

{include file="./_nav.tpl" current="stats"}

<nav class="tabs">
	<aside>
		{exportmenu right=true}
	</aside>
	<ul class="sub">
		<li class="current">{link href="stats.php" label="Tableaux"}</li>
		<li>{link href="graphs.php" label="Graphiques"}</li>
	</ul>
	<ul class="sub">
		<li{if $type === 'entry'} class="current"{/if}>{link href="?period=%s&type=entry"|args:$period label="Entrées"}</li>
		<li{if $type === 'exit'} class="current"{/if}>{link href="?period=%s&type=exit"|args:$period label="Sorties"}</li>
	</ul>
	<ul class="sub">
		<li{if $period === 'year'} class="current"{/if}>{link href="?period=year&type=%s"|args:$type label="Par année"}</li>
		<li{if $period === 'quarter'} class="current"{/if}>{link href="?period=quarter&type=%s"|args:$type label="Par trimestre"}</li>
	</ul>
</nav>


{if !$list->count()}
	<p class="alert block">Aucun vélo n'a été trouvé.</p>
{else}
	{include file="common/dynamic_list_head.tpl"}

	{foreach from=$list->iterate() item="row"}
		<tr style="{if $row.group === 'Total'}font-weight: bold{/if}">
			<th>
				{if $row.header}
					<h3>{$row.period}</h3>
				{/if}
			</th>
			<td>{$row.group}</td>
			<td class="num">{$row.count}</td>
			<td class="num">{$row.weight|weight:true} kg</td>
			<td class="actions"></td>
		</tr>
	{/foreach}
	</tbody>
	</table>
{/if}

{include file="_foot.tpl"}