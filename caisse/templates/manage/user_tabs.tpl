{include file="admin/_head.tpl" title="Notes" current="plugin_%s"|args:$plugin.id}

{include file="%s/manage/_nav.tpl"|args:$pos_templates_root}

{if $tabs === null}
	<p class="alert block">Aucun membre trouvé.</p>
{elseif !count($tabs)}
	<p class="alert block">Aucune note trouvée.</p>
{else}
	<table class="list">
		{foreach from=$tabs item="tab"}
		<tr>
			<th><a href="../tab.php?id={$tab.id}">Note n°{$tab.id}</a></th>
			<td>{$tab.opened|date}</td>
		</tr>
		{/foreach}
	</table>
{/if}

{include file="admin/_foot.tpl"}