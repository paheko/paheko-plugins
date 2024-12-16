{include file="_head.tpl" title="Catégories" current="plugin_pim" hide_title=true plugin_css=['calendar.css']}

<nav class="tabs">
	<aside>
		{linkbutton href="edit.php" label="Nouvelle catégorie" shape="plus" target="_dialog"}
	</aside>
	{if !$dialog}
	{linkbutton href="../" label="Retour à l'agenda" shape="left"}
	{/if}
</nav>

<table class="list categories">

	{foreach from=$list item="cat"}
		<tr>
			<td class="color" style="--hue: {$cat.color}"></td>
			<th style="--hue: {$cat.color}">{$cat.title}</th>
			<td>{if $cat.is_default}(par défaut){/if}</td>
			<td class="actions">
				{if $cat.is_default}{linkbutton href="?set_default=%d"|args:$cat.id label="Par défaut" shape="check"}{/if}
				{linkbutton href="delete.php?id=%d"|args:$cat.id label="Supprimer" shape="delete"}
				{linkbutton href="edit.php?id=%d"|args:$cat.id label="Modifier" shape="edit"}
			</td>
		</tr>
	{/foreach}

</table>

{include file="_foot.tpl"}
