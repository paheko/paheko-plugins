{include file="_head.tpl" title="Notes"}

{include file="./_nav.tpl"}

{if !count($tabs)}
	<p class="alert block">Aucune note trouvée.</p>
{else}
	<table class="list">
		{foreach from=$tabs item="tab"}
		<tr>
			<th><a href="../tab.php?id={$tab.id}">Note n°{$tab.id}</a></th>
			<td>{$tab.opened|date}</td>
			<td>{$tab.name}</td>
		</tr>
		{/foreach}
	</table>
{/if}

{include file="_foot.tpl"}