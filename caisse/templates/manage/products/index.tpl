{include file="_head.tpl" title="Gestion produits"}

{include file="../_nav.tpl" current='products'}

<nav class="tabs">
	<aside>
		{if $archived}
			{linkbutton shape="eye" label="Voir les produits non archivés" href="?"}
		{else}
			{linkbutton shape="eye-off" label="Voir seulement les produits archivés" href="?archived=1"}
		{/if}
		{exportmenu right=true}
	</aside>
</nav>

<form action="" method="get" class="shortFormLeft">
	<p>{input type="search" name="q" placeholder="Nom du produit" default=$search} {button type="submit" label="Chercher" shape="right"}</p>
</form>

<form method="post" action="">

{include file="common/dynamic_list_head.tpl" check=true}
	<?php $category = null; ?>
		{foreach from=$list->iterate() item="row"}
		<tr>
			<td class="check">{input type="checkbox" name="selected[]" value=$row.id}</td>
			<td>
				{if $category !== $row.category}
					<?php $category = $row->category; ?>
					{$row.category}
				{/if}
			</td>
			<th scope="row">{$row.name}</th>
			<td class="money">{if $row.price < 0}<span class="alert">{/if}{$row.price|escape|money_currency}{if $row.price < 0}</span>{/if}</td>
			<td class="num">{$row.qty}</td>
			<td class="actions">
				{if $row.stock !== null}{linkbutton href="history.php?id=%d"|args:$row.id label="Historique du stock" shape="calendar"}{/if}
				{linkbutton href="edit.php?id=%d"|args:$row.id label="Modifier" shape="edit"}
				{linkbutton href="edit.php?id=%d&delete"|args:$row.id label="Supprimer" shape="delete" target="_dialog"}
			</td>
		</tr>
	{/foreach}
	</tbody>
	<tfoot>
		<tr>
			<td class="check"><input type="checkbox" value="Tout cocher / décocher" id="f_all2" /><label for="f_all2"></label></td>
			<td class="actions" colspan="5">
				<em>Pour les produits cochés :</em>
				{csrf_field key=$csrf_key}
				<select name="action">
					<option value="">— Choisir une action à effectuer —</option>
					{if $archived}
						<option value="archive">Désarchiver</option>
					{else}
						<option value="archive">Archiver</option>
					{/if}
					<option value="delete">Supprimer</option>
				</select>
				<noscript>
					<input type="submit" value="OK" />
				</noscript>
			</td>
		</tr>
	</tfoot>
</table>
</form>

{$list->getHTMLPagination()|raw}


{include file="_foot.tpl"}