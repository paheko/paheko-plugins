{include file="_head.tpl" title="%s vélos sortis du stock"|args:$total}

{include file="./_nav.tpl" current="historique"}

{include file="common/dynamic_list_head.tpl"}

	{foreach from=$list->iterate() item="row"}
		<tr>
			<th class="num"><a href="fiche.php?id={$row.id}">{$row.id}</a></th>
			<td>{$row.etiquette}</td>
			<td>{$row.type}</td>
			<td>{$row.roues}</td>
			<td>{$row.genre}</td>
			<td>{$row.modele}</td>
			<td>{$row.couleur}</td>
			<td>{if empty($row.prix)}--{elseif $row.prix < 0}à&nbsp;démonter{else}{$row.prix} €{/if}</td>
			<td>{$row.date_sortie|date_short}</td>
			<td>{$row.raison_sortie}</td>
			<td class="actions"></td>
		</tr>
	{/foreach}
	</tbody>
</table>

{$list->getHTMLPagination()|raw}

{include file="_foot.tpl"}