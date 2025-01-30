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
		</p>
	</fieldset>
</form>

{include file="common/dynamic_list_head.tpl"}
	{foreach from=$list->iterate() item="velo"}
		<tr>
			<th class="num"><a href="fiche.php?id={$velo.id|escape}">{$velo.id|escape}</a></th>
			<td class="num"><a href="fiche.php?id={$velo.id|escape}">{$velo.etiquette|escape}</a></td>
			<td>{$velo.type|escape}</td>
			<td>{$velo.roues|escape}</td>
			<td>{$velo.genre|escape}</td>
			<td>{$velo.modele|escape}</td>
			<td>{$velo.couleur|escape}</td>
			<td>{if empty($velo.prix)}--{elseif $velo.prix < 0}à&nbsp;démonter{else}{$velo.prix|escape} €{/if}</td>
			<td>{$velo.date_entree|date_short}</td>
			<td class="actions"></td>
		</tr>
	{/foreach}
	</tbody>
</table>

{$list->getHTMLPagination()|raw}

{include file="_foot.tpl"}