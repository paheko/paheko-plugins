{include file="_head.tpl" title="%s vélos sortis du stock"|args:$total}

{include file="./_nav.tpl" current="historique"}

{include file="common/dynamic_list_head.tpl"}

	{foreach from=$list->iterate() item="row"}
		<tr>
			<th class="num"><a href="fiche.php?id={$row.id}">{$row.id}</a></th>
			{if $fields.etiquette.enabled}
			<td>{$row.etiquette}</td>
			{/if}
			{if $fields.type.enabled}
			<td>{$row.type}</td>
			{/if}
			{if $fields.roues.enabled}
			<td>{$row.roues}</td>
			{/if}
			{if $fields.genre.enabled}
			<td>{$row.genre}</td>
			{/if}
			{if $fields.modele.enabled}
			<td>{$row.modele}</td>
			{/if}
			{if $fields.couleur.enabled}
			<td>{$row.couleur}</td>
			{/if}
			{if $fields.prix.enabled}
			<td>{if empty($row.prix)}--{elseif $row.prix < 0}à&nbsp;démonter{else}{$row.prix} €{/if}</td>
			{/if}
			{if $fields.date_sortie.enabled}
			<td>{$row.date_sortie|date_short}</td>
			{/if}
			{if $fields.raison_sortie.enabled}
			<td>{$row.raison_sortie}</td>
			{/if}
			<td class="actions"></td>
		</tr>
	{/foreach}
	</tbody>
</table>

{$list->getHTMLPagination()|raw}

{include file="_foot.tpl"}