{include file="_head.tpl" title="%s vélos en stock"|args:$total}

{include file="./_nav.tpl" current="index"}

<form method="get" action="fiche.php" class="fastFind">
	<fieldset class="shortFormRight">
		<legend>Trouver un vélo par numéro unique</legend>
		<p>
			<input type="number" size="5" name="id" />
			<input type="submit" value="Trouver" />
		</p>
	</fieldset>
	<fieldset>
		<legend>Trouver un vélo par numéro d'étiquette</legend>
		<p>
			<input type="number" size="5" name="etiquette" />
			<input type="submit" value="Trouver" />
			{linkbutton shape="search" href="recherche.php" label="Recherche avancée"}
		</p>
	</fieldset>
</form>

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
			<td>{$row.date_entree|date_short}</td>
			<td class="actions"></td>
		</tr>
	{/foreach}
	</tbody>
</table>

{$list->getHTMLPagination()|raw}

{include file="_foot.tpl"}